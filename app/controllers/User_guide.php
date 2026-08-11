<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_guide extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->m_auth->check_login();
    }

    public function index()
    {
        $data['panel'] = '<i class="fa fa-book"></i> &nbsp;<b>User Guide</b>';
        $this->l_skin->main('skin/user_guide', $data);
    }
}
