<?php

namespace Nitm\Content\Behaviors;

/**
 * This implementation is highly inefficient!
 *
 * This behavior adds relations that could be supported by this class
 * For example a feature may include art || event || user informaiton  however this may not be known during runtime. This class adds all poentialy supported relations
 * The implementing class can also describe the dynamicContentConfig property to determine the keys for the relations This can come in two flavors:
 * Global
 *    [
 *       key => column,
 *       otherKey => column
 *    ]
 * Per relation
 *    [
 *       relation => [
 *          key => column,
 *          otherKey => column
 *          ],
 *          ...
 *    ].
 */
class SimpleDynamicContentRelation extends DynamicContentRelation
{
    protected function getModelClass($modelName, $namespace = null)
    {
        $namespace = $namespace ?: $this->getOwnerNamespace();
        $class = $namespace.'\\Simple'.$modelName;
        if (class_exists($class)) {
            return $class;
        } else {
            return '\\Nitm\\Content\\Models\\'.$modelName;
        }
    }
}
