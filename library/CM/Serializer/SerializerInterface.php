<?php

interface CM_Serializer_SerializerInterface {

    /**
     * @param mixed $data
     * @return mixed
     */
    public function serialize($data);

    /**
     * @param mixed $data
     * @return mixed
     */
    public function unserialize($data);

}
