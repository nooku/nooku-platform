<?php
/**
 * Kodekit Component - http://www.timble.net/kodekit
 *
 * @copyright	Copyright (C) 2011 - 2016 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		MPL v2.0 <https://www.mozilla.org/en-US/MPL/2.0>
 * @link		https://github.com/timble/kodekit-activities for the canonical source repository
 */

namespace Kodekit\Component\Activities;

use Kodekit\Library;

/**
 * Activity Template Helper.
 *
 * @author  Arunas Mazeika <https://github.com/amazeika>
 * @package Kodekit\Component\Activities
 */
class TemplateHelperActivity extends Library\TemplateHelperAbstract implements Library\ObjectMultiton, ActivityRendererInterface
{
    /**
     * Renders an activity.
     *
     * Wraps around {@link render()} to easily render activities on layouts.
     *
     * @param array $config An optional configuration array.
     * @return string The rendered activity.
     */
    public function activity($config = array())
    {
        $config = new Library\ObjectConfig($config);
        $config->url = '';

        $output = '';

        if ($activity = $config->entity) {
            $output = $this->render($activity, $config);
        }

        return $output;
    }

    /**
     * Renders an activity.
     *
     * @param ActivityInterface $activity The activity object.
     * @param array             $config   An optional configuration array.
     * @return string The rendered activity.
     */
    public function render(ActivityInterface $activity, $config = array())
    {
        $config = new Library\ObjectConfig($config);
        $output = $activity->getActivityFormat();

        if (preg_match_all('/{(.*?)}/', $output, $labels))
        {
            $tokens = $activity->tokens;

            foreach ($labels[1] as $label)
            {
                $parts = explode(':', $label);

                if (isset($tokens[$parts[0]]))
                {
                    $object = $tokens[$parts[0]];

                    // Deal with context translations.
                    if (isset($parts[1]))
                    {
                        $object = clone $object;
                        $object->setDisplayName($parts[1]);
                    }

                    if ($object = $this->_renderObject($object, $config)) {
                        $output = str_replace('{' . $label . '}', $object, $output);
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Renders an activity object.
     *
     * @param ActivityObjectInterface $object The activity object.
     * @param Library\ObjectConfig    $config The configuration object.
     * @return string The rendered object.
     */
    protected function _renderObject(ActivityObjectInterface $object, Library\ObjectConfig $config)
    {
        $config->append(array(
            'url'  => null,
            'html' => true,
            'fqr'  => false,
            'escaped_urls' => true,
        ));

        if ($output = $object->getDisplayName())
        {
            if ($config->html)
            {
                $output  = $object->getDisplayName();
                $attribs = $object->getAttributes() ? $this->buildAttributes($object->getAttributes()) : '';

                if ($url = $object->getUrl())
                {
                    // Make sure we have a fully qualified route.
                    if ($config->fqr && !$url->getHost())
                    {
                        if(!$config->url instanceof Library\HttpUrl) {
                            $config->url = Library\HttpUrl::fromString($config->url);
                        }

                        $url->setUrl($config->url->toString(Library\HttpUrl::AUTHORITY));
                    }

                    $url    = $url->toString(Library\HttpUrl::FULL, $config->escaped_urls);
                    $output = "<a {$attribs} href=\"{$url}\">{$output}</a>";
                }
                else $output = "<span {$attribs}>{$output}</span>";
            }
        }
        else $output = '';

        return $output;
    }
}
