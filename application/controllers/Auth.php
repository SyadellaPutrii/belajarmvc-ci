<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

   public function __construct()
   {
      parent::__construct();
      $this->load->library('form_validation');
   }
    public function index()
    {
       $this->form_validation->set_rules('email','Email', 'trim|required|valid_email');
       $this->form_validation->set_rules('password','Password', 'trim|required');

       if($this->form_validation->run() == false) {
       $data['title'] = 'Login Page';
       $this->load->view('templates/auth_header', $data);
       $this->load->view('auth/login');
       $this->load->view('templates/auth_footer'); 
      } 

      else {
         //validation is true or success
         $this->_login();
      }
    }

    private function _login()
    {
      $email = $this->input->post('email');
      $password = $this->input->post('password');

      $user = $this->db->get_where('user', ['email' => $email])->row_array();

      if($user) 
      {
         //Jika usernya benar
         if ($user['is_active'] = 1)
         {
            if (password_verify($password, $user['password'])) {
               //Jika Passwordnya benar
               $data = [
                  'email' => $user['email'],
                  'role_id' => $user['role_id']
               ];
               $this->session->set_userdata($data);
               if($user['role_id'] ==1) {
                  redirect('admin');
               } else {
                  redirect('user');
               }
            } else {
               //Jika passwordnya salah
               $this->session->set_flashdata('message', '<div class ="alert alert-danger" role="alert">Invalid password.</div>'
               );
               redirect('auth');
            }
         } else {
            //Jika Usernya belum Aktivasi
            $this->session->set_flashdata('message', '<div class ="alert alert-danger" role="alert">This email has not been Activated.</div>'
               );
               redirect('auth');
         }
      } else {
         //Jika Usenya tidak ada di table user
         $this->session->set_flashdata('message', '<div class ="alert alert-danger" role="alert">Email is not Registered.</div>'
               );
               redirect('auth');
      }   
    }

    public function registration()
    {

      //$this->form_validation->set_rules(
         //'name', 'Name',
         //'//required|trim'
      //);

      $this->form_validation->set_rules(
         'email', 'Email',
         'required|trim|valid_email|is_unique[user.email]',
         [
            'is_unique' => 'This Email has already registered!'
         ]
         );

      $this->form_validation->set_rules(
         'password1', 'Password',
         'required|trim|min_length[3]|matches[password2]',
         [
            'matches' => 'Password does not match!',
            'min_length' => 'Password to short'
         ]
         );

      $this->form_validation->set_rules(
         'password2', 'Password',
         'required|trim|matches[password1]'
      );

      if($this->form_validation->run() == false)
      {
         $data["title"] = 'Registration Page';
         $this->load->view('templates/auth_header', $data);
         $this->load->view('auth/registration');
         $this->load->view('templates/auth_footer');
      }
    else
    {
      $data = [
         'name' => htmlspecialchars($this->input->post('name', true)),
         'email' => htmlspecialchars($this->input->post('email', true)),
         'image' => 'default.jpg',
         'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
         'role_id' => 2,
         'is_active' => 1,
         'date_actived' => time()
      ];
    

      $this->db->insert('user', $data);
      $this->session->set_flashdata(
         'message',
         '<div class="alert alert-success" role="alert">
               Congratulation! Your Account has been Created. Please Login.
               </div>'
      );

      redirect('auth');

      
   } 
   }
} 