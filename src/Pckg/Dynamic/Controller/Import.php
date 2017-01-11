<?php namespace Pckg\Dynamic\Controller;

use League\Csv\Reader;
use Pckg\Collection;
use Pckg\Database\Entity;
use Pckg\Database\Relation\HasMany;
use Pckg\Database\Repository;
use Pckg\Dynamic\Form\Import as ImportForm;
use Pckg\Dynamic\Record\Field;
use Pckg\Dynamic\Record\Record;
use Pckg\Dynamic\Record\Table;
use Pckg\Dynamic\Service\Dynamic;
use Pckg\Dynamic\Service\Export as ExportService;
use Pckg\Dynamic\Service\Export\Strategy;
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

    public function postImportTableAction(Table $table, Dynamic $dynamicService, ImportForm $importForm)
    {
        $entity = $table->createEntity();
        $upload = new Upload('file');

        if (($success = $upload->validateUpload()) !== true) {
            return [
                'success' => false,
                'message' => $success,
            ];
        }

        $csv = Reader::createFromString($upload->getContent());
        //$csv->setDelimiter(';');
        $headers = $csv->fetchOne();
        $data = collect($csv->setOffset(1)->fetchAll());

        $mapped = $data->map(
            function($row) use ($headers) {
                $data = [];

                foreach ($headers as $key => $field) {
                    $data[$field] = $row[$key];
                }

                return $data;
            }
        );

        $availableFields = $table->listableFields(
            function(HasMany $fields) {
                $fields->realFields();
            }
        );
        $uniqueFields = $availableFields->filter(
            function(Field $field) {
                return in_array($field->dynamic_field_type_id, [1, 6]); // id, slug
            }
        );

        $mapped->each(
            function($item) use ($uniqueFields, $availableFields, $entity, $table) {
                $locales = [];
                foreach ($item as $field => $value) {
                    if ($pos = strpos($field, '*')) {
                        $locale = substr($field, $pos + 1);
                        $locales[$locale] = $locale;
                    }
                }

                $prevRecord = null;
                foreach ($locales as $locale) {
                    $uniqueValues = [];
                    $uniqueFields->each(
                        function(Field $field) use ($item, &$uniqueValues) {
                            if (array_key_exists($field->field, $item)) {
                                $uniqueValues[$field->field] = $item[$field->field];
                            }
                        }
                    );

                    $values = [];
                    $availableFields->each(
                        function(Field $field) use ($item, &$values, $locale) {
                            if (array_key_exists($field->field . '*' . $locale, $item)) {
                                $values[$field->field] = $item[$field->field . '*' . $locale];

                            } elseif (array_key_exists($field->field, $item)) {
                                $values[$field->field] = $item[$field->field];

                            }
                        }
                    );

                    runInLocale(
                        function() use ($uniqueFields, $table, $values, $uniqueValues, $locale, &$prevRecord) {
                            $entity = $table->createEntity();
                            if (!$prevRecord && $uniqueFields && $uniqueValues) {
                                /**
                                 * Check for existing records.
                                 */
                                $record = $prevRecord = Record::getOrCreate($uniqueValues, $entity);
                            } elseif (!$prevRecord) {
                                /**
                                 * Create new record.
                                 */
                                $record = $prevRecord = new Record();
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
                        },
                        $locale
                    );
                }
            }
        );

        return $this->response()->respondWithSuccessRedirect();
    }

}