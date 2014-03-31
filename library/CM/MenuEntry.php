<?php

class CM_MenuEntry {

    /**  @var CM_Menu|null */
    protected $_submenu = null;

    /** @var array */
    protected $_data = array();

    /** @var CM_MenuEntry|null */
    protected $_parent = null;

    /** @var CM_Menu */
    protected $_menu = null;

    /**
     * @param array             $data
     * @param CM_Menu           $menu
     * @param CM_MenuEntry|null $parent
     * @throws CM_Exception_Invalid
     */
    public final function __construct(array $data, CM_Menu $menu, CM_MenuEntry $parent = null) {
        $this->_data = $data;
        $this->_parent = $parent;
        $this->_menu = $menu;

        if (!isset($data['page'])) {
            throw new CM_Exception_Invalid('Page param has to be set');
        }

        if (!isset($data['label'])) {
            throw new CM_Exception_Invalid('Menu label has to be set');
        }

        if (isset($data['submenu'])) {
            $this->_submenu = new CM_Menu($data['submenu'], $this);
        }
    }

    /**
     * @param string $path
     * @param array  $params
     * @return bool True if path/queries match
     */
    public final function compare($path, array $params = array()) {
        /** @var CM_Page_Abstract $pageClass */
        $pageClass = $this->getPageName();
        if ($path == $pageClass::getPath() && array_intersect_assoc($this->getParams(), $params) == $this->getParams()) {
            return true;
        }
        return false;
    }

    /**
     * @return CM_Menu|null
     */
    public final function getChildren() {
        return $this->_submenu;
    }

    /**
     * @return int Entry depth (starting by 0)
     */
    public final function getDepth() {
        return count($this->getParents());
    }

    /**
     * @return string|null
     */
    public final function getClass() {
        return $this->getField('class');
    }

    /**
     * @return string|null
     */
    public final function getIcon() {
        return $this->getField('icon');
    }

    /**
     * @return string|null
     */
    public final function getIndication() {
        return $this->getField('indication');
    }

    /**
     * @param string $name
     * @return string|null
     */
    public final function getField($name) {
        if (!isset($this->_data[$name])) {
            return null;
        }
        return (string) $this->_data[$name];
    }

    /**
     * @return string Entry label
     */
    public final function getLabel() {
        return $this->_data['label'];
    }

    /**
     * @return string Returns page class name
     */
    public final function getPageName() {
        return $this->_data['page'];
    }

    /**
     * @return array Params list
     */
    public final function getParams() {
        if (isset($this->_data['params'])) {
            return $this->_data['params'];
        } else {
            return array();
        }
    }

    /**
     * @return CM_MenuEntry|null
     */
    public final function getParent() {
        return $this->_parent;
    }

    /**
     * @return CM_MenuEntry[]
     */
    public final function getParents() {
        $parents = array();
        if ($this->hasParent()) {
            $parents = $this->getParent()->getParents();
            $parents[] = $this->getParent();
        }

        return $parents;
    }

    /**
     * @return CM_Menu
     */
    public final function getSiblings() {
        return $this->_menu;
    }

    /**
     * @return bool
     */
    public final function hasChildren() {
        return !empty($this->_submenu);
    }

    /**
     * @return bool
     */
    public final function hasParent() {
        return (bool) $this->_parent;
    }

    /**
     * @param string    $path
     * @param CM_Params $params
     * @return bool
     */
    public final function isActive($path, CM_Params $params) {
        if ($this->compare($path, $params->getAllOriginal())) {
            return true;
        }

        if ($this->hasChildren()) {
            foreach ($this->getChildren()->getAllEntries() as $entry) {
                if ($entry->isActive($path, $params)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getHash() {
        $params = $this->getParams();
        ksort($params);
        return hash('crc32', $this->getPageName() . ':' . $this->getDepth() . ':' . json_encode($params));
    }

    /**
     * @return bool
     */
    public final function isViewable() {
        $params = $this->getParams();
        return !isset($params['viewable']) || true === $params['viewable'];
    }
}
