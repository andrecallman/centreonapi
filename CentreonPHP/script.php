<?php	
	include('Centreon.class.php');
	
	$capi = new Centreon('localhost',80,'http','admin','admingw');
	
	//print_r($capi->show_token());
	
	//print_r($capi->acl_lastReload('d-m-Y H:i:s'));
	
	//$capi->acl_reload();
	
	//$capi->api_call('OBJECT','ACTION','VALUES');
	//$capi->api_call(null,'ACTION','VALUES');
	//$capi->api_call(null,null,'VALUES');
	
	//print_r($capi->acl_getAclGroups());
	
	//print_r($capi->acl_getAclGroup('name','Testes'));
	//$capi->acl_addAclGroup('NOVO_TESTE','Novo Teste');
	//$capi->acl_setParam('NOVO_TESTE','activate','0');
	//$capi->acl_setParam('NOVO_TESTE','activate','1');
	
	//print_r($capi->acl_getMenus('NOVO_TESTE'));
	//print_r($capi->acl_getMenus('ALL'));
	//print_r($capi->acl_getActions('ALL'));
	//print_r($capi->acl_getActions('NOVO_TESTE'));
	
	//$capi->jsonPretty();
	
	print_r($capi->acl_lastReload('Y-m-d'));



	
	
	