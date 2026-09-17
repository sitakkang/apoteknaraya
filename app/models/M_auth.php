<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class M_auth extends CI_Model {
	public function __construct(){
            parent::__construct();
	}

	function get_session($id)
	{
		$data = array(
			'log' => $this->session->userdata('sess_log'),
			'id' => $this->session->userdata('sess_id'),
			'name' => $this->session->userdata('sess_name'),
            'level' => $this->session->userdata('sess_level'),
			'avatar' => $this->session->userdata('sess_avatar')
		);
		return $data[$id];
	}

    /**
     * Level user AKTIF diambil langsung dari tabel conf_users.
     * Sengaja tidak memakai session (sess_level) supaya perubahan level di
     * database langsung berlaku walau session masih lama.
     *
     * @param  int|null $id_user default: session id_user (sess_id)
     * @return int 0 bila user tidak ditemukan
     */
    function level_user($id_user = NULL)
    {
        if ($id_user === NULL) {
            $id_user = $this->session->userdata('sess_id');
        }

        $row = $this->db->select('level')
                        ->get_where('conf_users', array('id_user' => intval($id_user)))
                        ->row();

        return $row ? intval($row->level) : 0;
    }

    /**
     * Apakah level user aktif termasuk daftar level yang diizinkan?
     *
     * Contoh: $this->m_auth->level_in(array(1, 2, 3));
     */
    function level_in($levels = array(1, 2, 3), $id_user = NULL)
    {
        $levels = array_map('intval', (array) $levels);
        return in_array($this->level_user($id_user), $levels, TRUE);
    }

    function avatar()
    {
        if(empty($this->session->userdata('sess_avatar'))){
            return base_url('img/noimage.png');
        }else{
            return base_url($this->session->userdata('sess_avatar'));
        }
    }

    // HAK AKSES CONTROLLERS
	function check_login(){
		if(empty($this->session->userdata('sess_log'))){redirect(base_url());}
	}

    function check_superadmin(){
        if($this->session->userdata('sess_level') != 1){redirect(base_url());}
    }

	// HAK AKSES MENU
    // function check_akses()
    // {
	// 	if($this->get_session('log') == TRUE){
	// 		$uri = $this->uri->segment(1).'/'.$this->uri->segment(2);
	// 		$LevelUser = $this->session->userdata('sess_level');
	// 		$query_menu = $this->db->query('SELECT id_menu FROM conf_menu WHERE status=1 AND sub=1 AND link="'.$uri.'" AND level LIKE "%'.$LevelUser.'%" LIMIT 1');
	// 		$query_sub = $this->db->query('SELECT id_submenu FROM conf_submenu WHERE status=1 AND link="'.$uri.'" AND level LIKE "%'.$LevelUser.'%" LIMIT 1');
	// 		if($query_menu->num_rows() < 1) {
	// 			if($query_sub->num_rows() < 1) {
	// 				redirect(base_url());
	// 			}
	// 		}
	// 	}else{
	// 		redirect(base_url());
	// 	}
	// }
    function check_akses()
    {
        if ($this->get_session('log') == TRUE) {
            $uri                                = $this->uri->segment(1);

            $LevelUser                          = $this->session->userdata('sess_level');
            $query_menu                         = $this->db->query(
                "
                    SELECT id_menu 
                    FROM conf_menu 
                    WHERE status = 1  
                    AND sub      = 1 
                    AND link='" . $uri . "' 
                    AND level LIKE '%" . $LevelUser . "%' LIMIT 1"
            );
            $query_sub = $this->db->query(
                "
                    SELECT id_submenu 
                    FROM conf_submenu 
                    WHERE status = 1  
                    AND link='" . $uri . "'
                    AND level LIKE '%" . $LevelUser . "%' LIMIT 1"
            );
            if ($query_menu->num_rows() < 1) {
                if ($query_sub->num_rows() < 1) {
                    redirect(base_url());
                }
            }
        } else {
            redirect(base_url());
        }
	}

	// TOKEN CSRF
    function gen_token()
    {
    	$token = $this->l_help->rand_str1(5, FALSE);
    	$this->session->set_tempdata('datatoken', $token, 3600);
    	return '<input type="hidden" name="datatoken" value="'.base64_encode($token).'">';
    }

    function check_token($token)
    {
    	$data_token = base64_decode($token);
    	$sess_token = $this->session->tempdata('datatoken');
    	if($data_token === $sess_token){
    		return TRUE;
    	}else{
    		return FALSE;
    	}
    	$this->session->unset_tempdata('datatoken');
    }

    function menu_sidebar()
    {
        $LevelUser = $this->session->userdata('sess_level');
        $query_menu = $this->db->query('SELECT * FROM conf_menu WHERE akses=1 AND status=1 AND level LIKE "%'.$LevelUser.'%" ORDER BY position ASC');
        $query_sub = $this->db->query('SELECT * FROM conf_submenu WHERE status=1 AND level LIKE "%'.$LevelUser.'%" ORDER BY position ASC');
        if ($query_menu->num_rows() > 0)
        {
            foreach ($query_menu->result() as $menu)
            {
                if($menu->sub == 2){
                    echo '<li><a href="javascript:;" data-sidenav-dropdown-toggle><span class="sidenav-link-icon"><i class="fa '.$menu->icon.'"></i></span><span class="sidenav-link-title">'.$menu->name.'</span><span class="sidenav-dropdown-icon show" data-sidenav-dropdown-icon><i class="fa fa-angle-down"></i></span><span class="sidenav-dropdown-icon" data-sidenav-dropdown-icon><i class="fa fa-angle-up"></i></span></a>'."\n";
                    echo '<ul class="sidenav-dropdown" data-sidenav-dropdown>';
                    foreach ($query_sub->result() as $sub)
                    {
                        if($menu->id_menu == $sub->id_menu)
                        {
                            echo '<li><a href="'.site_url().$sub->link.'"><i class="fa '.$sub->icon.'"></i>&nbsp;&nbsp;'.$sub->name.'</a></li>'."\n";
                        }
                    }
                    echo '</ul></li>';
                }else{
                    echo '<li><a href="'.site_url().$menu->link.'"><span class="sidenav-link-icon"><i class="fa '.$menu->icon.'"></i></span><span class="sidenav-link-title">'.$menu->name.'</span></a></li>';
                }
            }
        }
        else{ return NULL; }
    }

}