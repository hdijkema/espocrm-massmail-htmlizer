<?php
namespace Espo\Modules\MassMailHtmlizer\Services;

use Espo\ORM\Entity;
use \Espo\Core\Htmlizer\Htmlizer;

class MassEmail extends \Espo\Modules\Crm\Services\MassEmail
{
    private $standard_relations = [ 'queueItems', 'inboundEmail', 'excludingTargetLists', 'targetLists', 'campaign', 'modifiedBy', 'createdBy', 'emailTemplate' ];

    protected function init()
    {
        parent::init();
        $this->addDependency('fileManager');
        $this->addDependency('acl');
        $this->addDependency('metadata');
        $this->addDependency('serviceFactory');
        $this->addDependency('dateTime');
        $this->addDependency('number');
        $this->addDependency('entityManager');

	#$this->targetsLinkList = ['contacts', 'users'];
    }

    protected function getPreparedEmail(
       Entity $queueItem, Entity $massEmail, Entity $emailTemplate, Entity $target, $trackingUrlList = [])
    {
       $email = parent::getPreparedEmail($queueItem, $massEmail, $emailTemplate, $target, $trackingUrlList);

       $body = $email->get('body');
       $subject = $email->get('subject');

       foreach($massEmail->getRelationList() as $relation) {
           if (!in_array($relation, $this->standard_relations)) {
              # Process this relation
              $entity = $massEmail->get($relation);
              if ($entity != null) {
                  foreach($entity->getAttributeList() as $attribute) {
                      $data = $entity->get($attribute);
                      $body = str_ireplace('{'.$relation.'.'.$attribute.'}', $data, $body);
                      $subject = str_ireplace('{'.$relation.'.'.$attribute.'}', $data, $subject);
                  }
              }
           }
       }

       $email->set('body', $body);
       $email->set('subject', $subject);

       return $email;
    }

    protected function createHtmlizer()
    {
        return new Htmlizer(
            $this->getFileManager(),
            $this->getInjection('dateTime'),
            $this->getInjection('number'),
            $this->getAcl(),
            $this->getInjection('entityManager'),
            $this->getInjection('metadata'),
            $this->getInjection('defaultLanguage'),
            $this->getInjection('config')
        );
    }
}
