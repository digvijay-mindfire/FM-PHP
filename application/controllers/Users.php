<?php defined('BASEPATH') OR exit('No direct script access allowed');

use airmoi\FileMaker\FileMaker; // these are namesapce of filemaker which will be declare here so no need to touch it also
use airmoi\FileMaker\FileMakerException;
use airmoi\FileMaker\FileMakerValidationException;
require  __DIR__ . '/../libraries/filemaker/autoloader.php'; // we are includding heree file

class Users extends CI_Controller {
    
    function __construct() {
        parent::__construct();
		
		// Load form validation ibrary & user model
        $this->load->library('form_validation');
        $this->load->model('user');
		
		// User login status
		$this->isUserLoggedIn = $this->session->userdata('isUserLoggedIn');
		$options=['prevalidate' => true];
		$this->fm = new FileMaker(filemaker_database, filemaker_host, filemaker_username, filemaker_password);
        // here we are calling filemaker class to use in our program. 
    }
	
	public function index(){
        if($this->isUserLoggedIn){
			redirect('users/account');
        }else{
            redirect('users/login');
        }
    }

    public function account(){
        $data = array();
        if($this->isUserLoggedIn){
			$con = array(
				'id' => $this->session->userdata('userId')
			);
            $data['user'] = $this->user->getTabledata($con,$this->fm)[0]; 
            // here we are passing filemaker parameter to model because i need to fetch table record of users let go in model file
           
			// Pass the user data and load view
			$this->load->view('elements/header', $data);
			$this->load->view('users/account', $data);
			$this->load->view('elements/footer');
        }else{
            redirect('users/login');
        }
		
		$options=['prevalidate' => true];
		$this->fm = new FileMaker(filemaker_database, filemaker_host, filemaker_username, filemaker_password);
    }

    public function login(){
		if($this->isUserLoggedIn){
			redirect('users/account');
		}
        $data = array();
        
        // Get messages from the session
        if($this->session->userdata('success_msg')){
            $data['success_msg'] = $this->session->userdata('success_msg');
            $this->session->unset_userdata('success_msg');
        }
        if($this->session->userdata('error_msg')){
            $data['error_msg'] = $this->session->userdata('error_msg');
            $this->session->unset_userdata('error_msg');
        }
		
		// If login request submitted
        if($this->input->post('loginSubmit')){
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'password', 'required');
			
            if($this->form_validation->run() == true){
                $con = array(
					'returnType' => 'single',
					'conditions' => array(
						'email'=> $this->input->post('email'),
						'password' => md5($this->input->post('password')),
						//'status' => 1
					)
                );
                $checkLogin = $this->user->getTabledata($con,$this->fm);
				
                if($checkLogin){
                    $this->session->set_userdata('isUserLoggedIn', TRUE);
                    $this->session->set_userdata('userId', $checkLogin[0]['id']);
                    redirect('users/account/');
                }else{
                    $data['error_msg'] = 'Wrong email or password, please try again.';
                }
            }else{
				$data['error_msg'] = 'Please fill all the mandatory fields.';
			}
        }
		
		// Load view
		$this->load->view('elements/header', $data);
		$this->load->view('users/login', $data);
		$this->load->view('elements/footer');
    }

    public function registration(){
		if($this->isUserLoggedIn){
			redirect('users/account');
		}
        $data = $userData = array();
		
		// If registration request is submitted
        if($this->input->post('signupSubmit')){
            $this->form_validation->set_rules('first_name', 'First Name', 'required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|callback_email_check');
            $this->form_validation->set_rules('password', 'password', 'required');
            $this->form_validation->set_rules('conf_password', 'confirm password', 'required|matches[password]');

            $userData = array(
                'first_name' => strip_tags($this->input->post('first_name')),
				'last_name' => strip_tags($this->input->post('last_name')),
                'email' => strip_tags($this->input->post('email')),
                'password' => md5($this->input->post('password')),
                'gender' => $this->input->post('gender'),
                'phone' => strip_tags($this->input->post('phone'))
            );

            if($this->form_validation->run() == true){
                $insert = $this->user->insert($userData,$this->fm);
                if($insert){
                    $this->session->set_userdata('success_msg', 'Your account registration has been successful. Please login to your account.');
                    redirect('users/login');
                }else{
                    $data['error_msg'] = 'Some problems occured, please try again.';
                }
            }else{
				$data['error_msg'] = 'Please fill all the mandatory fields.';
			}
        }
		
		// Posted data
        $data['user'] = $userData;
		
		// Load view
		$this->load->view('elements/header', $data);
		$this->load->view('users/registration', $data);
		$this->load->view('elements/footer');
    }
    
    public function logout(){
        $this->session->unset_userdata('isUserLoggedIn');
        $this->session->unset_userdata('userId');
        $this->session->sess_destroy();
        redirect('users/login/');
    }
    
    
	// Existing email check during validation
    public function email_check($str){
        $con = array(
			'returnType' => 'count',
			'conditions' => array(
				'email' => $str
			)
		);
        $checkEmail = $this->user->getRows($con);
        if($checkEmail > 0){
            $this->form_validation->set_message('email_check', 'The given email already exists.');
            return FALSE;
        }else{
            return TRUE;
        }
    }
}
