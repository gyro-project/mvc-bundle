<?php

namespace Gyro\MVC;

use InvalidArgumentException;

/**
 * View Model Base class.
 *
 * Target for properties and view logic passed to any templating mechanism
 * or serialization method. Returning a ViewStruct from a controller
 * is catched by the ViewListener and transformed into a Twig template
 * for example:
 *
 *      # View/Default/HelloView.php
 *      class HelloView extends ViewStruct
 *      {
 *          public $name;
 *
 *          public function reverseName()
 *          {
 *              return strrev($this->name);
 *          }
 *      }
 *
 *      # Controller/DefaultController.php
 *
 *      {
 *          public function helloAction($name)
 *          {
 *              return new HelloView(array('name' => $name));
 *          }
 *      }
 *
 *      # Resources/views/Default/hello.html.twig
 *      Hello {{ view.name }} or {{ view.reverseName() }}!
 */
abstract class ViewStruct
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            if (! property_exists($this, $property)) {
                $this->throwPropertyNotExists($property);
            }

            $this->$property = $value;
        }
    }

    /** @return mixed */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        $this->throwPropertyNotExists($name);
    }

    private function throwPropertyNotExists(string $property): void
    {
        throw new InvalidArgumentException(
            'View ' . static::class . ' does not support property "$' . $property .
            ' The following properties exist: ' . implode(", ", array_keys(get_object_vars($this)))
        );
    }
}
