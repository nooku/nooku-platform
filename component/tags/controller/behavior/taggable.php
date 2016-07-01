<?php
/**
 * Kodekit Component - http://www.timble.net/kodekit
 *
 * @copyright	Copyright (C) 2011 - 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link		https://github.com/timble/kodekit-tags for the canonical source repository
 */

namespace Kodekit\Component\Tags;

use Kodekit\Library;

/**
 * Taggable Controller Behavior
 *
 * @author  Johan Janssens <http://github.com/johanjanssens>
 * @package Kodekit\Component\Tags
 */
class ControllerBehaviorTaggable extends Library\BehaviorAbstract
{
    public function __construct(Library\ObjectConfig $config)
    {
        parent::__construct($config);

        $this->addCommandCallback('after.add'    , '_setTags');
        $this->addCommandCallback('after.edit'   , '_setTags');
        $this->addCommandCallback('after.delete' , '_removeTags');
    }
    
    /**
     * Set the tags for an entity
     *
     * If the request data contains a tags array, it will be used as the new tag list.
     * If the tags field is an empty string, all entity tags are deleted and no new ones are added.
     *
     * @param Library\ControllerContextModel $context
     * @return bool
     */
    protected function _setTags(Library\ControllerContextModel $context)
    {
        $entity = $context->result;
        $data   = $context->getRequest()->getData();

        if ($data->has('tags'))
        {
            if ($entity->isIdentifiable() && !$context->response->isError())
            {
                $tags = $entity->getTags();
    
                $package = $this->getMixer()->getIdentifier()->package;
                if(!$this->getObject('com:'.$package.'.controller.tag')->canAdd()) {
                    $status  = Library\Database::STATUS_FETCHED;
                } else {
                    $status = null;
                }
    
                //Delete tags
                if(count($tags))
                {
                    $tags->delete();
                    $tags->clear();
                }
    
                //Create tags
                if($entity->tags)
                {
                    foreach ($entity->tags as $tag)
                    {
                        $properties = array(
                            'title' => $tag,
                            'row'   => $entity->uuid,
                        );
    
                        $tags->insert($properties, $status);
                    }
                }
    
                $tags->save();
    
                return true;
            }
        }
    }

    protected function _removeTags(Library\ControllerContextModel $context)
    {
        $collection = $context->entity;

        if($collection->isIdentifiable())
        {
            foreach ($collection as $entity)
            {
                if($entity->getStatus() != $entity::STATUS_DELETED) {
                    $entity->getTags()->delete();
                }
            }
        }
    }
}
