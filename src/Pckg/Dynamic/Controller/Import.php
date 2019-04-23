<?php namespace Pckg\Dynamic\Controller;

use League\Csv\Reader;
use Pckg\Database\Record;
use Pckg\Database\Relation\HasMany;
use Pckg\Dynamic\Form\Import as ImportForm;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Export as ExportService;
use Pckg\Framework\Controller;
use Pckg\Manager\Upload;

class Import extends Controller
{

    /**
     * @var ExportService
     */
    protected $exportService;

    protected $dynamic;

    public function __construct(ExportService $exportService, Dynamic $dynamic)
    {
        $this->exportService = $exportService;
        $this->dynamic = $dynamic;
    }

    public function getImportTableAction(Table $table, Dynamic $dynamicService, ImportForm $importForm)
    {
        $availableFields = $table->listableFields(
            function(HasMany $listableFields) {
                $listableFields->realFields();
            }
        );

        $uniqueFields = $availableFields->filter(
            function(Field $field) {
                return in_array($field->dynamic_field_type_id, [1, 6]); // id, slug
            }
        );

        return view(
            'import/index',
            [
                'table'        => $table,
                'fields'       => $availableFields,
                'uniqueFields' => $uniqueFields,
                'importForm'   => $importForm,
            ]
        );
    }

    public function postUploadFileAction(Table $table)
    {
        $upload = new Upload('file');

        if (($success = $upload->validateUpload()) !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $delimiters = [',', ';', '|'];
        $content = $upload->getContent();
        $headers = [];
        $delimiter = ';';
        $newline = '\n';
        foreach ($delimiters as $d) {
            $csv = Reader::createFromString($content);
            $csv->setDelimiter($d);
            $tempHeaders = $csv->fetchOne(0);
            if (count($tempHeaders) > count($headers)) {
                $headers = $tempHeaders;
                $delimiter = $d;
            }
        }

        $columns = $table->fields->filter(function(Field $field) use ($headers) {
            return in_array($field->field, $headers) && $field->isImportable();
        })->rekey()->map(function(Field $field) {
            return $field->field;
        });

        $file = $upload->save(path('tmp'));

        return [
            'success' => true,
            'meta'    => [
                'headers'   => $headers,
                'columns'   => $columns,
                'delimiter' => $delimiter,
                'rows'      => $csv->count(),
                'newsline'  => $newline,
                'file' => $file,
            ],
        ];
    }

    public function postImportFileAction(Table $table)
    {
        $meta = post('meta');
        $file = $meta['file'];
        $delimiter = $meta['delimiter'];
        $csv = Reader::createFromPath(path('tmp') . $file);
        $csv->setDelimiter($delimiter);
        $import = $this->importContent($table, $csv);

        return [
            'meta'    => post('meta'),
            'success' => true,
        ];
    }

    public function importContent(Table $table, Reader $csv)
    {
        $data = $csv->getIterator();
        $headers = [];
        //$csv->setHeaderOffset(1);

        $availableFields = $table->listableFields(function(HasMany $fields) {
            $fields->realFields();
        });
        $arrAvailableFields = $availableFields->map('field')->all();

        $mapped = [];
        // id, title, title*en, title*de, description (all), test, *notok
        foreach ($data as $i => $row) {
            if ($i == 0) {
                $headers = collect($row)->filter(function($field) use ($arrAvailableFields) {
                    if (!trim($field)) {
                        return false;
                    }

                    $asterisk = strpos($field, '*');

                    if ($asterisk === false) {
                        return in_array($field, $arrAvailableFields);
                    }

                    if ($asterisk === 0) {
                        // exported relation
                        return false;
                    }

                    // translated value
                    $expl = explode('*', $field);
                    return in_array($expl, $field[0]);
                });
                continue;
            }

            $d = [];
            foreach ($headers as $key => $field) {
                $d[$field] = $row[$key];
            }
            $mapped[] = $d;
        }

        $uniqueFields = $availableFields->filter(function(Field $field) {
            return in_array($field->dynamic_field_type_id, [1, 6]); // id, slug
        });
        $defaultLocales = $this->localeManager()->getFrontendLanguages()->keyBy('slug')->map('slug')->all();

        collect($mapped)->each(function($item) use ($uniqueFields, $availableFields, $table, $defaultLocales) {
            $locales = [];
            foreach ($item as $field => $value) {
                if ($pos = strpos($field, '*')) {
                    $locale = substr($field, $pos + 1);
                    $locales[$locale] = $locale;
                }
            }

            if (!$locales) {
                $locales = $defaultLocales;
            }

            $prevRecord = null;
            foreach ($locales as $locale) {
                $uniqueValues = [];
                $uniqueFields->each(function(Field $field) use ($item, &$uniqueValues) {
                    if (array_key_exists($field->field, $item)) {
                        $uniqueValues[$field->field] = $item[$field->field];
                    }
                });

                $values = [];
                $availableFields->each(function(Field $field) use ($item, &$values, $locale) {
                    if (array_key_exists($field->field . '*' . $locale, $item)) {
                        $values[$field->field] = $item[$field->field . '*' . $locale];
                    } elseif (array_key_exists($field->field, $item)) {
                        $val = $item[$field->field];
                        if ($field->fieldType->slug == 'geo') {
                            $val = explode(';', $val);
                            $val = ['x' => $val[0], 'y' => $val[1]];
                        }
                        $values[$field->field] = $val;
                    }
                });

                if (!$values) {
                    continue;
                }

                runInLocale(function() use ($uniqueFields, $table, $values, $uniqueValues, $locale, &$prevRecord) {
                    $entity = $table->createEntity();
                    if (!$prevRecord && $uniqueFields && $uniqueValues) {
                        /**
                         * Check for existing records.
                         */
                        $record = $prevRecord = Record::getOrNew($uniqueValues, $entity);
                    } elseif (!$prevRecord) {
                        /**
                         * Create new record.
                         */
                        $record = $prevRecord = new Record([], $entity);
                    } else {
                        /**
                         * Update translation.
                         */
                        $record = $prevRecord;
                    }

                    $record->set($values);

                    /**
                     * Save record.
                     */
                    $record->save($entity);
                }, $locale);
            }
        });
    }

    public function postImportTableAction(Table $table, ImportForm $importForm)
    {
        $entity = $table->createEntity();
        $upload = new Upload('file');

        if (($success = $upload->validateUpload()) !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $this->importContent($table, Reader::createFromString($upload->getContent()));

        return $this->response()->respondWithSuccessRedirect();
    }

}