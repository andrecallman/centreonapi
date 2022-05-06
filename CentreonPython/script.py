import cmd
from unicodedata import name
from Centreon import Centreon

capi = Centreon(host='172.16.252.44', login='admin', password='9YnqkPpbM1Vz')

host = 'PLANNEXO_MONITORACAO'
template = 'plannexo'


client_id = '126'
cliente = 'INSTITUTO SOCRATES GUANAES - ISG'


# JOBNIGHT
service_prefix = 'Status Job Night: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_jobnight'
args='!http://172.16.252.44:9090/api/v1/query!{}!24!24!0'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

#JOBNIGHT TIME
service_prefix = 'JobNight Time: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_jobnight_time'
args='!{}!3600!7200!0'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

#DATA REQUISICAO
service_prefix = 'Data Requisicao: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_data_req'
args='!172.16.250.219:8304!{}!24!48'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

# SAIDAS ONTEM
service_prefix = 'Saidas Ontem: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_saidas_ontem'
args='!http://172.16.252.44:9090/api/v1/query!{}!30!40'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

# DATA ORDEM
service_prefix = 'Data Ordem: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_data_ordem'
args='!http://172.16.252.44:9090/api/v1/query!{}!48!48!1'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

# STATUS APROV SUG
service_prefix = 'Status Aprov. Sugestao: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_aprov_sug'
args='!http://172.16.252.44:9090/api/v1/query!{}!24!24!1'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

# SUGESTAO COMPRAS
service_prefix = 'Sugest. de compras: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_sugestao_compras'
args='!http:/172.16.252.44:9090//api/v1/query!{}!80!70'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

# QUANTIDADE SKUs
service_prefix = 'Quantidade SKUs: '
service_name = service_prefix+cliente
command = 'gw_check_plannexo_sku'
args='!http://172.16.252.44:9090//api/v1/query!{}!70!60'.format(client_id)
capi.service_add(host=host,name=service_name,template=template)
capi.service_setcommand(host=host,name=service_name,command=command,args=args)
capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)

# BALANCEAMENTO DE ESTOQUE
level = 0
for faixa in ['Zero','Muito Baixo','Minimo','Alvo','Maximo','Muito Alto']:
    service_prefix = 'Balanc. Estoque [{}]: '.format(faixa)
    service_name = service_prefix+cliente
    command = 'gw_check_plannexo_bal_estoque'
    args='!http://172.16.252.44:9090/api/v1/query!{}!{}!30!40'.format(client_id,level)
    capi.service_add(host=host,name=service_name,template=template)
    capi.service_setcommand(host=host,name=service_name,command=command,args=args)
    capi.service_setgroup(group='NOTIF_BIONEXO_PLANNEXO',host=host,name=service_name)
    level += 1


capi.applycfg()
pass
