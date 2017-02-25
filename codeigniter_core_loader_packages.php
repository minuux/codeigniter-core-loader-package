<?php

defined('BASEPATH') OR exit('No direct script access allowed');

trait codeigniter_core_loader_packages {

    public function package($package_name, $params, $object_name = NULL) {
        $packages = config_item('packages');
        $package = (object) $packages[$package_name];
        switch ($package->type) {
            case 'codeigniter-library':
                return $this->_library($package, $params, $object_name);
            case 'codeigniter-view':
                //视图中的object_name是用来设置是否返回view的
                return $this->_view($package, $params, $object_name === NULL ? FALSE : TRUE);
            case 'codeigniter-helper':
                return $this->_helper($package);
            case 'codeigniter-model':
                return $this->_model($package, $object_name);
        }

        return $this;
    }

    private function _library($package, $params = NULL, $object_name = NULL) {
        include_once($package->path);
        return $this->_ci_init_library($package->class_name, '', $params, $object_name);
    }

    private function _view($package, $vars = array(), $return = FALSE) {
        return $this->_ci_load(array('_ci_path' => $package->path, '_ci_vars' => $vars, '_ci_return' => $return));
    }

    private function _helper($package) {
        if (!isset($this->_ci_helpers[$package->name])) {
            include_once($package->path);
            $this->_ci_helpers[$package->name] = TRUE;
        }
    }

    private function _model($package, $object_name = NULL) {
        if (in_array($package->name, $this->_ci_models, TRUE)) {
            return $this;
        }
        $CI = & get_instance();
        if (isset($CI->$object_name)) {
            show_error('The model name you are loading is the name of a resource that is already being used: ' . $object_name);
        }
        if (!class_exists('CI_Model', FALSE)) {
            load_class('Model', 'core');
        }
        require_once($package->path);
        $this->_ci_models[] = $package->name;
        $CI->$object_name = new $package->class_name();
        return $this;
    }

}
