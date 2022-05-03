<?php

	class Centreon {
		private $host = '';
		private $login = '';
		private $password = '';
	    private $token = '';
		private $url='';
		private $output_space = 28;
		private $pretty = null;
		private $response_codes = array(
			'200' => '[200] Successful',
			'400' => '[400] Missing parameter | Missing name parameter | Unknown parameter | Objects are not linked',
			'401' => '[401] Unauthorized',
			'403' => '[403] Unauthorized',
			'404' => '[404] Object not found | Method not implemented into Centreon API',
			'409' => '[409] Object already exists | Name is already in use | Objects already linked',
			'500' => '[500] Internal server error'
		);
		private $actions = array(
			"*",
			"global_event_handler",
			"global_flap_detection",
			"global_host_checks",
			"global_host_obsess",
			"global_host_passive_checks",
			"global_notifications",
			"global_perf_data",
			"global_restart",
			"global_service_checks",
			"global_service_obsess",
			"global_service_passive_checks",
			"global_shutdown",
			"host_acknowledgement",
			"host_checks",
			"host_checks_for_services",
			"host_comment",
			"host_event_handler",
			"host_flap_detection",
			"host_notifications",
			"host_notifications_for_services",
			"host_schedule_check",
			"host_schedule_downtime",
			"host_schedule_forced_check",
			"host_submit_result",
			"poller_listing",
			"poller_stats",
			"service_acknowledgement",
			"service_checks",
			"service_comment",
			"service_event_handler",
			"service_flap_detection",
			"service_notifications",
			"service_passive_checks",
			"service_schedule_check",
			"service_schedule_downtime",
			"service_schedule_forced_check",
			"service_submit_result",
			"top_counter"
		); 
		
		function __construct($host='localhost',$port=80,$protocol='http',$username='',$password='') {
			
			$this->username = $username;
			$this->password = $password;
			
			$data = array(
				'username' => $this->username,
				'password' => $this->password
			);
			
			$headers = array(
				'Content-Type: application/x-www-form-urlencoded'
			);
			
			$this->url = "{$protocol}://{$host}:{$port}/centreon/api/index.php";
			
			$ch = curl_init("{$this->url}?action=authenticate");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
			$response = curl_exec($ch);
			
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
				exit(2);
			}else{
				$this->token = json_decode($response)->authToken;
			}
			
			curl_close($ch);
		}
		
		public function jsonPretty(){
			$this->pretty = JSON_PRETTY_PRINT;
		}
		
		public function show_token(){
			return $this->token;
		} 
		
		public function api_call($obj=null,$action=null,$values=null,$output=FALSE){			
			$headers = array(
				"Content-Type: application/json",
				"centreon-auth-token: {$this->token}"
			);
			
			$data = array(
				'action' => $action,
				'object' => $obj,
				'values' => $values
			);
			
			$ch = curl_init("{$this->url}?action=action&object=centreon_clapi");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			
			$response = curl_exec($ch);
			
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
				exit(2);
			}else{
				if ($output){			
					return json_decode($response);
				}else{
					$code = $this->response_codes[curl_getinfo($ch,CURLINFO_HTTP_CODE)];
					echo str_pad("[{$obj}][{$action}]:",$this->output_space,' ').$code."\n";
				}
			}
			curl_close($ch);
		}
			
		// ACL
		
		// Reload ACL Configurations
		public function acl_reload(){
			$this->api_call('ACL','reload');
		}	
		
		// Get last ACL reload date time (Rest API doesn't return any data)
		public function acl_lastReload($format=''){			
			return shell_exec("centreon -u {$this->username} -p {$this->password} -o ACL -a lastreload -v '{$format}'");
		}
		
		
		// ACL Groups
		
		// Get ACL Groups
		public function acl_getAcl(){
			return json_encode($this->api_call('ACLGROUP','show',null,TRUE),$this->pretty);
		}
		
		// Get specific ACL Group
		public function acl_getAclGroup($key,$value){
			$reponse = json_decode($this->acl_getAcl(),TRUE);
			
			$items = [];
			
			foreach ($response['result'] as $response){
				if ($response[$key]==$value){
					$items[] = $response;
				}
			}
			
			return json_encode($items,$this->pretty);
		}
		
		// Add ACL Group
		public function acl_addAclGroup($name,$alias){
			$values = "{$name};{$alias}";
			
			$this->api_call('ACLGROUP','add',$values);
		}
		
		// Delete ACL Group
		public function acl_delAclGroup($name){
			$values = "{$name}";
			
			$this->api_call('ACLGROUP','del',$values);
		}
		
		// Set ACL Group Parameters
		public function acl_setParam($name,$param,$value){
			$values = "{$name};{$param};{$value}";
			
			$params = ['name','alias','activate'];	
			
			if(in_array($param,$params)){				
				$this->api_call('ACLGROUP','setparam',$values);
			}else{
				echo "Invalid Parameter. Try ".implode(', ',$params);	
			}	
		}
		
		// Get ACL linked menus
		public function acl_getAclMenus($name){
			$values = "{$name}";

			return json_encode($this->api_call('ACLGROUP','getmenu',$values,TRUE),$this->pretty);
		}
		
		// Get ACL linked actions
		public function acl_getAclActions($name){
			$values = "{$name}";

			return json_encode($this->api_call('ACLGROUP','getaction',$values,TRUE),$this->pretty);
		}
		
		// Get ACL Resources
		public function acl_getAclResources($name){
			$values = "{$name}";

			return json_encode($this->api_call('ACLGROUP','getresource',$values,TRUE),$this->pretty);
		}
		
		// Get ACL contacts
		public function acl_getAclContacts($name){
			$values = "{$name}";

			return json_encode($this->api_call('ACLGROUP','getcontact',$values,TRUE),$this->pretty);
		}
		
		// Get ACL contact groups
		public function acl_getAclContactGroups($name){
			$values = "{$name}";

			return json_encode($this->api_call('ACLGROUP','getcontactgroup',$values,TRUE),$this->pretty);
		}
		
		// Set ACL menu link
		public function acl_setAclMenu($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','setmenu',$values);
		}
		
		// Set ACL action link
		public function acl_setAclAction($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','setaction',$values);
		}	
		
		// Set ACL resource link
		public function acl_setAclResource($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','setresource',$values);
		}	

		// Add ACL menu link
		public function acl_addAclMenu($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','addmenu',$values);
		}
		
		// Add ACL action link
		public function acl_addAclAction($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','addaction',$values);
		}	
		
		// Add ACL resource link
		public function acl_addAclResource($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','addresource',$values);
		}
		
		// Del ACL menu link
		public function acl_delAclMenu($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','delmenu',$values);
		}
		
		// Del ACL action link
		public function acl_delAclAction($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','delaction',$values);
		}	
		
		// Del ACL resource link
		public function acl_delAclResource($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','delresource',$values);
		}

		// Set ACL contact link
		public function acl_setAclContact($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','setcontact',$values);
		}

		// Add ACL contact link
		public function acl_addAclContatct($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','addcontact',$values);
		}

		// Del ACL contact link
		public function acl_delAclContact($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','delcontact',$values);
		}

		// Set ACL contact group link
		public function acl_setAclContactGroup($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','setcontactgroup',$values);
		}

		// Add ACL contact group link
		public function acl_addAclContactGroup($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','addcontactgroup',$values);
		}

		// Del ACL contact group link
		public function acl_delAclContactGroup($name,$items){
			$values = "{$name};{$items}";

			$this->api_call('ACLGROUP','delcontactgroup',$values);
		}

		// Get ACL Actions
		public function acl_getActions(){
			return json_encode($this->api_call('ACLACTION','show',null,TRUE),$this->pretty);
		}

		// Get specific ACL Action
		public function acl_getAction($key,$value){
			$response = json_decode($this->acl_getActions(),TRUE);
			
			$items = [];
			
			foreach ($response['result'] as $respose){
				if ($response[$key]==$value){
					$items[] = $response;
				}
			}

			return json_encode($items,$this->pretty);
		}
		
		// Add ACL Action
		public function acl_addAction($name,$alias){
			$values = "{$name};{$alias}";

			$this->api_call('ACLACTION','add',$values);
		}
		
		// Del ACL Action
		public function acl_delAction($name){
			$values = "{$name}";

			$this->api_call('ACLACTION','del',$values);
		}
		
		// ACL Action set param
		public function acl_setParamAction($name,$param,$value){
			$values = "{$name};{$param};{$value}";
			
			$params = ['name','description','activate'];	
			
			if(in_array($param,$params)){				
				$this->api_call('ACLACTION','setparam',$values);
			}else{
				echo "Invalid Parameter. Try ".implode(', ',$params);	
			}			
		}
		
		// Get ACL Groups linked to this Action
		public function acl_getAclActionGroup($name){
			$values = "{$name}";
			
			return json_encode($this->api_call('ACLACTION','getaclgroup',null,TRUE),$this->pretty);
		}
		
		// Grant Actions to a ACL Action
		public function acl_grantAclAction($name,$items){
			
			foreach(explode('|',$items) as $item){
				$values = "{$name};{$item}";
				
				if(in_array($item,$this->actions)){				
					$this->api_call('ACLACTION','grant',$values);
				}else{
					echo "Invalid Action. Try ".implode(', ',$this->actions);	
				}
			}
		}
		
		// Grant Actions to a ACL Action
		public function acl_revokeAclAction($name,$items){
			
			foreach(explode('|',$items) as $item){
				$values = "{$name};{$item}";
				
				if(in_array($item,$this->actions)){				
					$this->api_call('ACLACTION','revoke',$values);
				}else{
					echo "Invalid Action. Try ".implode(', ',$this->actions);	
				}
			}
		}

		
		
   }
 