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
        $csv->setDelimiter(';');
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
            function($item) use ($uniqueFields, $entity) {
                $values = [];
                $uniqueFields->each(
                    function(Field $field) use ($item, &$values) {
                        if (array_key_exists($field->field, $item)) {
                            $values[$field->field] = $item[$field->field];
                        }
                    }
                );

                if ($uniqueFields) {
                    /**
                     * Check for existing records.
                     */
                    $record = Record::getOrCreate($values, $entity);
                    $record->set($item);
                } else {
                    /**
                     * Create new record.
                     */
                    $record = new Record($item);
                }

                /**
                 * Save record.
                 */
                $record->save($entity);
            }
        );

        return $this->response()->respondWithSuccessRedirect();
    }

}