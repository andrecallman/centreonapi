from unittest import TestSuite
import requests

# Desabilita alertas de certificado 
import urllib3
urllib3.disable_warnings()
###

class Centreon:
    errors = {
        '200': '[200] Successful',
        '400': '[400] Missing parameter | Missing name parameter | Unknown parameter | Objects are not linked',
        '401': '[401] Unauthorized',
        '403': '[403] Unauthorized',
        '404': '[404] Object not found | Method not implemented into Centreon API',
        '409': '[409] Object already exists | Name is already in use | Objects already linked',
        '500': '[500] Internal server error'
    }

    def __init__(self, host, login, password,prot='http',port=80):
        port = str(port)
        self.url = f'{prot}://{host}:{port}/centreon/api/index.php'

        self.token = ''
        params = {
            "action": "authenticate"
        }

        data = {
            'username': login,
            'password': password
        }

        response = requests.post(self.url, data=data, params=params,verify=False)

        if response.status_code == 200:
            self.token = response.json()['authToken']
        else:
            print(self.errors[str(response.status_code)])
            exit()

    def showtoken(self):
        '''Mostra o token gerado para conexão'''

        print(self.token)

    def apicall(self, obj, action, obj_params, output=False):
        params = {
            'action': 'action',
            'object': 'centreon_clapi'
        }

        headers = {
            'Content-Type': 'application/json',
            'centreon-auth-token': self.token
        }

        data = {
            "action": action,
            "object": obj,
            "values": obj_params
        }

        response = requests.post(self.url, params=params, headers=headers, json=data,verify=False)

        if not output:
            print("[{1}][{0}]:\t\t\t {2}".format(action, obj, self.errors[str(response.status_code)]))
        else:
            return response.json()['result']


    def applycfg(self,poller='',output=False):
        '''Reinicia todos os pollers'''
        poller = 1 if poller =='' else poller

        str_apply = f'{poller}'  

        return self.apicall(obj='',action='APPLYCFG', obj_params=poller,output=output)

        

# HOSTS
    def host_get(self,name=''):
        '''Mostra hosts presentes no Centreon'''

        str_get_host = f'{name}'

        host = self.apicall('HOST','show',str_get_host)

        return host

        
    def host_add(self,name,alias,ip,template='generic-host',poller='Central',group='HOSTS_NO_NOTIFY'):
        '''Adiciona um novo host ao Centreon'''
        
        str_add_host = f'{name};{alias};{ip};{template};{poller};{group}'
        
        self.apicall('host','add',str_add_host)
    
    def host_setgroup(self, group, host):
        '''Adiciona um host a um grupo de hosts'''

        str_setgroup = f'{group};{host}'

        self.apicall('HG','addmember',str_setgroup)

# SERVICES
    def service_get(self, host='', name=''):
        '''Mostra serviços presentes no Centreon'''

        str_show_service = f'{name}'
        services = self.apicall('service','show',str_show_service,output=True)
        
        output = []
        
        if host:
            for service in services:
                if service['host name'] == host:
                    output.append(service)

            return output    

        return services        

        
    def service_add(self, host, name, template='generic-service'):
        '''Adiciona um novo service a um host ja criado'''

        str_add_svc = f"{host};{name};{template}"
        self.apicall('service', 'add', str_add_svc)

    def service_setcommand(self, host, name, command, args):
        '''Define comando e argumentos a ser utilizado pelo serviço'''

        str_set_cmd = f"{host};{name};check_command;{command}"
        str_set_args = f"{host};{name};check_command_arguments;{args}"
        self.apicall('service', 'setparam', str_set_cmd)
        self.apicall('service', 'setparam', str_set_args)

    def service_setgroup(self, group, host, name):
        '''Define o grupo de serviços ao qual pertence o serviço'''
        
        str_set_group = f"{group};{host},{name}"
        self.apicall("SG", "addservice", str_set_group)
       
    def service_settemplate(self,host,name,template):
        '''Define template de um determinado service'''

        str_settemplate = f'{host};{name};template;{template}'
        
        self.apicall('service','setparam',str_settemplate)
        
        

# COMMANDS
    def command_add (self,name,cmdline):
        '''Adiciona um novo command ao centreon'''

        str_set_command = f'{name};check;{cmdline}'
        
        
        self.apicall('cmd','add',str_set_command)


# TEMPLATES
    def template_add(self, name, alias,template='generic-service'):
        '''Adiciona um novo service template  ao centreon'''

        str_add_tpl = f'{name};{alias};{template}'

        self.apicall('stpl','add',str_add_tpl)

    def template_setcommand(self, name, command, args):
        '''Configura um command em um template'''

        str_setcommand = f'{name};check_command;{command}'
        str_setargs = f'{name};check_command_arguments;{args}'

        self.apicall('stpl', 'setparam', str_setcommand)
        self.apicall('stpl', 'setparam', str_setargs)

    






    def acl_lastreoad(self):
        str_values = 'd-m-Y H:i:s'

        return self.apicall('ACL','lastreload',str_values,True)