##--system info: module_owner="##section##" module="##modId##" system="1"--##
##-- 
Шаблоны модуля подключаются и наследуются в следующем порядке:

  Гипермодуль -> Конфигурация -> Инстанция(модуль).

  1. Шаблон гипермодуля "Информационная лента" является общим для всех модулей этого типа, templates/hyper/ami_multifeeds.tpl
     В нем содержится все основное оформление для всех модулей такого типа.
     
  2. Шаблон конфигурации "Фотоальбомы" содержит и переопределяет только элементы оформления, которые должны
     отличаться от базового модуля и являются специфичными для модулей этой конфигурации. /hyper/ami_multifeeds_photoalbum.tpl

  3. Шаблон инстанции. Шаблон конкретного экземпляра модуля, в котором вы сейчас находитесь. 
     В нем содержатся элементы оформления специфичные для этой инстанции.

В любом шаблоне доступны переменные ##AMI_CONF_ID## и ##AMI_MOD_ID## для определения текущей работающей конфигурации и модуля. 
Эти переменный можно использовать в формировании имени классов CSS или в условных конструкциях ##IF()## для того, чтобы избежать ненужных
переопределений элементов оформления в шаблонах конфигурации и инстанции.
   
  Пример: class="##AMI_MOD_ID##_item". Имя класса элемента оформления определенного в шаблоне Гипермодуля или Конфигурации будет содержать 
  название конкретного модуля.  

  
Templates are including and are inherited in the following order:

  Hypermodule -> Configuration -> Instance (the module).

  1. Template of hypermodule "Information Tape" is common to all the modules of this type, templates/hyper /ami_multifeeds.tpl
     It contains all the basic design for all modules of this type.
     
  2. Template of configuration "PhotoGallery" contains and overrides only design elements that must
     are different from the base module and are specific to the modules of this configuration. /hyper/ami_multifeeds_photoalbum.tpl

  3. Template of instance. Template of specific instance of the module in which you are now.
     It contains design elements specific to this instance.

In every template are available the variables ##AMI_CONF_ID## and ##AMI_MOD_ID## to determine the current running configuration and module.
These variables can be used to form the name of the CSS classes or in the conditions ##IF()## in order to avoid unnecessary
redefinition of design elements in a template of configuration or in a template of instance.
   
  Example: class = "##AMI_MOD_ID##_item". Class name of CSS of the design element defined in template of hypermodule or in template of configuration will contain
  the name of the specific module.  
  
--##

##--
Подключаемый шаблон гипермодуля "Информационная лента" "ami_multifeeds.tpl"

Included template of hypermodule "Information Tape" "ami_multifeeds.tpl"
--##
%%include_template "templates/hyper/ami_multifeeds.tpl"%%

##--
Подключаемый шаблон кофигурации "Фотоальбомы" "ami_multifeeds_photoalbum.tpl"

Included template of configuration "PhotoGallery" "ami_multifeeds_photoalbum.tpl"
--##
%%include_template "templates/hyper/ami_multifeeds_photoalbum.tpl"%%
%%include_language "templates/lang/modules/##modId##.lng"%%
