<?php

class CM_Util_NamedArgs {

    /**
     * @param ReflectionFunctionAbstract|Closure|callable $function
     * @param array                                       $args
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function matchNamedArgs($function, array $args) {
        $reflectionFunction = $this->instantiateReflection($function);
        $finalArgs = [];
        foreach ($reflectionFunction->getParameters() as $parameter) {
            if (array_key_exists($parameter->getName(), $args)) {
                $finalArgs[] = $args[$parameter->getName()];
                unset($args[$parameter->getName()]);
                continue;
            }
            if ($parameter->isDefaultValueAvailable()) {
                $finalArgs[] = $parameter->getDefaultValue();
                continue;
            }
            throw new CM_Exception_Invalid('Cannot find value for parameter', null, ['parameter' => $parameter->getName()]);
        }
        if (count($args) > 0) {
            $argNames = join(', ', array_keys($args));
            throw new CM_Exception_Invalid('Unmatched arguments', null, ['argNames' => $argNames]);
        }
        return $finalArgs;
    }

    /**
     * @param ReflectionFunctionAbstract|closure|callable $function
     * @throws CM_Exception_Invalid
     * @return ReflectionFunctionAbstract
     */
    public function instantiateReflection($function) {
        if ($function instanceof ReflectionFunctionAbstract) {
            return $function;
        }
        if ($function instanceof Closure) {
            return new ReflectionFunction($function);
        }
        if (is_callable($function)) {
            if (is_array($function)) {
                list($className, $methodName) = $function;
                return new ReflectionMethod($className, $methodName);
            }
            if (is_string($function)) {
                return new ReflectionFunction($function);
            }
        }
        throw new CM_Exception_Invalid('Cannot instantiate reflection');
    }
}
