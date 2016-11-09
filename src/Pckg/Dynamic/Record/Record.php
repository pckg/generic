<?php namespace Pckg\Dynamic\Record;

use Pckg\Database\Entity as DatabaseEntity;
use Pckg\Database\Record as DatabaseRecord;
use Pckg\Database\Repository;
use Pckg\Dynamic\Entity\Entity;
use Pckg\Dynamic\Record\Snippet\RecordActions;
use Pckg\Maestro\Service\Contract\Record as MaestroRecord;

class Record extends DatabaseRecord implements MaestroRecord
{

    use RecordActions {
        forceDelete as forceDeleteExtension;
        delete as deleteExtension;
    }

    protected $entity = Entity::class;

    /**
     * @return mixed
     */
    public function forceDelete(Entity $entity = null, Repository $repository = null)
    {
        return $this->forceDeleteExtension($entity, $repository);
    }

    /**
     * @return mixed
     */
    public function delete(DatabaseEntity $entity = null, Repository $repository = null)
    {
        /**
         * @T00D00 - this should be checked via entity!
         */
        if ($this->hasKey('deleted_at')) {
            return $this->deleteExtension($entity, $repository);

        } else {
            return $this->forceDeleteExtension($entity, $repository);

        }
    }

}