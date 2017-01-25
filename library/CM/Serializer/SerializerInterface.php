<?php

interface CM_Serializer_SerializerInterface {

    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data);

    /**
     * @param string $data
     * @return mixed
     */
    public function unserialize($data);

}
