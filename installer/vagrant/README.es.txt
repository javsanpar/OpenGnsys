
Preparar entorno virtual de desarrollo para OpenGnsys
=====================================================


Ficheros de configuración disponibles:

 - Vagrantfile-trunk-vbox  Vagrantfile para OpenGnsys oficial con proveedor VirtualBox.
 - Vagrantfile-devel-vbox  Vagrantfile para OpenGnsys en desarrollo con proveedor VirtualBox.
 - Vagrantfile-boottools-vbox  Vagrantfile para preparar el entorno de generación del cliente ogLive (recomendado solo para desarrolladores experimentados).
 - Vagrantfile-ogagent-vbox    Vagrantfile para preparar el entorno de desarrollo del agente OGAgent (recomendado solo para miembros del grupo de desarrollo).


Requisitos previos.

 - Instalar Oracle VM VirtualBox 5.0 con su Extension Pack.
 - Instalar la última versión oficial de Vagrant (no usar la incluida en Ubuntu 14.04).


El entorno de trabajo de OpenGnsys

 - ogAdministrator: MV para servidor OpenGnsys basada en Ubuntu 14.04 y 2º disco para repositorio.
 - ogClient01: MV cliente modelo con Ubuntu 14.04 instalado.
 - ogClient02 - ogclient0N: MV clientes para restaurar con disco vacío.


Ejecutar el entorno virtual (Vagrantfile-trunk y Vagrantfile-devel).

 - Crear un directorio de trabajo.
 - Copiar el fichero Vagrantfile correspondiente en dicho directorio como Vagrantfile.
 - Opcional: editar las variables de configuración del fichero Vagrantfile para el entorno personal.
   - LANGUAGE: idioma (se aceptan es_ES, ca_ES y en_GB).
   - NCLIENTS: nº de clientes a generar (de 2 a 9).
   - REPODISK, REPOSIZE: fichero y tamaño (en GB) del disco duro virtual para el repositorio de imágenes.
   - SERVERMEM, CLIENTMEM: memoria virtual (en MB) para servidor y clientes.
   - NETPREFIX: prefijo para las direcciones IP de la red virtual.
   - MACPREFIX: prefijo para las direcciones MAC de los clientes.
   - LOCALWEBPORT: puerto local para acceder al web de administración del servidor.
 - Opcional: para una definición automática del aula virtual con sus 2 clientes, descomentar las líneas del fichero Vagrantfile de los comandos "mysql" y "setclientmode".

 - Iniciar la MV del servidor:
	vagrant up
 - Iniciar las MV de los clientes (tras iniciar el servidor):
   - Cliente modelo:
	vagrant up ogClient01
   - Clientes vacíos para restaurar:
	vagrant up ogClient0X      (siendo X de 2 al nº máximo definido)

Notas:
 - Los procesos de inicio pueden tardar varios minutos en la primera ejecución, porque descargan y configuran las máquinas virtuales.
 - Antes de iniciar las MV de los clientes, debe accederse a la web de OpenGnsys para crear el aula e incorporar los equipos (o revisar que los datos son correctos).
 - Ignorar los errores de conexión de Vagrant con los clientes vacíos.


Descripción de las MV.

 - Máquina virtual para servidor OpenGnsys.
   - Debe estar iniciada en primer lugar y activa para gestionar los clientes.
   - Usuario de acceso SSH: vagrant, clave: vagrant.
   - La interfaz 2 de VirtualBox está definida en la red privada para las MV del entorno.
   - Instalación de OpenGnsys Server con datos por defecto.
   - Acceder desde un navegador del host local a la web de OpenGnsys en la URL:
	https://localhost:8443/opengnsys/
   - Configurar el DHCP usando las direcciones MAC de los clientes según lo indicado en la definición de la interfaz 2 de cada MV en Virtual Box.

 - Máquinas virtuales para cliente modelo y clientes para clonar.
   - La interfaz 2 de cada MV VirtualBox está definida en la red privada del entorno.
   - Ignorar los posibles errores de conexión de Vagrant.
   - Usar VirtualBox para deshabilitar la interfaz 1 de la MV del cliente modelo.
   - Una vez desplegadas las MV deberán ser controladas directamente con VirtaulBox.

