<?php
namespace Espo\Modules\MassMailHtmlizer\Tools\MassEmail;

use Espo\ORM\Entity;
use Espo\Entities\Email;

class Processor 
{
    private $standard_relations = [ 'queueItems', 'inboundEmail', 'excludingTargetLists', 'targetLists', 'campaign', 'modifiedBy', 'createdBy', 'emailTemplate' ];

    public function getPreparedEmail(
        Email $email,
        Entity $massEmail
    ) : ?Email {

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

}
