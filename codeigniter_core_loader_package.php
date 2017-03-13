<?php

defined('BASEPATH') OR exit('No direct script access allowed');

trait codeigniter_core_loader_package {

    public function package($package_name, $params=array(), $object_name = NULL) {
        $packages = config_item('packages');
        $package = (object) $packages[$package_name];
        switch ($package->type) {
            case 'codeigniter-library' : $this->_package_library($package, $params, $object_name);
            case 'codeigniter-helper' : $this->_package_helper($package);
            case 'codeigniter-model' : $this->_package_model($package, $params);
            case 'codeigniter-view':
                //视图中的object_name是用来设置是否返回view的
                //load_view会直接返回load所以直接return
                return $this->_package_view($package, $params, is_null($object_name) ? FALSE : TRUE);
        }

        return $this;
    }

    private function _package_library($package, $params = NULL, $object_name = NULL) {
        include_once($package->path);
        $this->_ci_init_library($package->class_name, '', $params, $object_name);
    }

    private function _package_view($package, $vars = array(), $return = FALSE) {
        return $this->_ci_load(array('_ci_path' => $package->path, '_ci_vars' => $vars, '_ci_return' => $return));
    }

    private function _package_helper($package) {
        if (!isset($this->_ci_helpers[$package->name])) {
            include_once($package->path);
            $this->_ci_helpers[$package->name] = TRUE;
        }
    }

    private function _package_model($package, $object_name = NULL) {
        if (in_array($package->name, $this->_ci_models, TRUE)) {
            return $this;
        }
        if(empty($object_name)){
            $object_name=$package->class_name;
        }
        $CI = & get_instance();
        if (isset($CI->$object_name)) {
            show_error('codeigniter_core_loader_package:The model name you are loading is the name of a resource that is already being used: ' . $object_name);
        }
        if (!class_exists('CI_Model', FALSE)) {
            load_class('Model', 'core');
        }
        require_once($package->path);
        $this->_ci_models[] = $package->name;
        $CI->$object_name = new $package->class_name;
    }

}
