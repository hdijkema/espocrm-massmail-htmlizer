<?php

namespace Espo\Modules\MassMailHtmlizer\Tools\MassEmail;

use Espo\Core\ORM\EntityManager;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\ORM\Entity;

class Processor
{
    private const SKIPPED_RELATION_LIST = [
        'queueItems',
        'inboundEmail',
        'excludingTargetLists',
        'targetLists',
        'campaign',
        'modifiedBy',
        'createdBy',
        'emailTemplate',
    ];

    public function __construct(private EntityManager $entityManager)
    {}

    /**
     * @return array<string, Entity>
     */
    public function getEntityHash(MassEmail $massEmail): array
    {
        $entityHash = [];

        foreach ($massEmail->getRelationList() as $relation) {
            if (in_array($relation, self::SKIPPED_RELATION_LIST, true)) {
                continue;
            }

            $relationType = $massEmail->getRelationType($relation);

            if (
                $relationType !== Entity::BELONGS_TO &&
                $relationType !== Entity::BELONGS_TO_PARENT
            ) {
                continue;
            }

            $entity = $this->entityManager
                ->getRDBRepository($massEmail->getEntityType())
                ->getRelation($massEmail, $relation)
                ->findOne();

            if ($entity) {
                // Preserve the placeholder namespace used by the original
                // extension and existing templates, e.g.
                // {KnowledgeBaseArticle.name}.
                $entityHash[$entity->getEntityType()] = $entity;
            }
        }

        return $entityHash;
    }
}
