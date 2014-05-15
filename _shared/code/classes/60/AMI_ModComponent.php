<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModComponent.php 44766 2013-11-30 08:41:31Z Kolesnikov Artem $
 * @since     5.12.0
 */

/**
 * Module component action controller interface.
 *
 * Use AMI_ModComponent children, interface usage will be described later.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @see        AMI_ModComponent
 * @since      5.12.0
 * @todo       Describe usage
 */
interface AMI_iModComponent{
    /**
     * Sets unique component id.
     *
     * @param  string $serialId  Serial id
     * @return AMI_iModComponent
     * @see    AMI_Mod::addComponent()
     */
    public function setSerialId($serialId);

    /**
     * Sets root serial id.
     *
     * @param  string $serialId  Serial id
     * @return AMI_iModComponent
     * @see    AMI_ModComponent::addSubComponent()
     * @todo   Detect necessity
     */
/*
    public function setRootSerialId($serialId);
*/

    /**
     * Sets module id.
     *
     * @param  string $modId  Module id
     * @return AMI_iModComponent
     * @see    AMI_ModComponent::addSubComponent()
     */
    public function setModId($modId);

    /**
     * Returns component type.
     *
     * Example:
     * <code>
     * // AMI_ModFilter::getType()
     * public function getType(){
     *     return 'form_filter';
     * }
     * // AMI_ModList::getType()
     * public function getType(){
     *     return 'list';
     * }
     * // AMI_ModForm::getType()
     * public function getType(){
     *     return 'form';
     * }
     * </code>
     *
     * @return string
     * @todo   Rename to avoid problems from pm#4251?
     */
    public function getType();

    /**
     * Returns TRUE if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv();

    /**
     * Initialization.
     *
     * @return AMI_iModComponent
     * @see    AMI_Mod::init()
     */
    public function init();

    /**
     * Returns component view.
     *
     * @return AMI_View
     */
    public function getView();
}

/**
 * Module component action controller abstraction.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 */
abstract class AMI_ModComponent implements AMI_iModComponent{
    /**
     * Flag specifying to use model
     *
     * @var   bool
     * @since 5.14.8
     */
    protected $useModel = TRUE;

    /**
     * Array of sub components
     *
     * @var    array
     * @see    AMI_ModComponent::_getView()
     * @see    AMI_ModComponent::addSubComponent()
     * @amidev Temporary
     */
    protected $aSubComponents = array();

    /**
     * Component serial id
     *
     * @var  string
     */
    private $serialId = '';

    /**
     * Root component serial id
     *
     * @var  string
     * @todo Specify wtf
     */
    private $rootSerialId = '';

    /**
     * Module id
     *
     * @var string
     */
    private $modId = '';

    /**
     * Model
     *
     * @var mixed
     * @see AMI_ModComponent::getModel()
     */
    private $oModel;

    /**
     * Specifies to use view or AMI_ViewEmpty
     *
     * @var bool
     * @see AMI_ModComponent::_getView()
     * @amidev
     */
    protected $bDispayView = false;

    /**
     * Component type postfix
     *
     * @var string
     * @amidev
     */
    protected $postfix = '';

    /**
     * Sets unique component id.
     *
     * @param  string $serialId  Serial id
     * @return AMI_ModComponent
     * @amidev Temporary
     */
    public function setSerialId($serialId){
        $this->serialId = (string)$serialId;
        return $this;
    }

    /**
     * Sets root serial id.
     *
     * @param  string $serialId  Serial id
     * @return AMI_ModComponent
     * @amidev Temporary
     */
    public function setRootSerialId($serialId){
        $this->rootSerialId = (string)$serialId;
        return $this;
    }

    /**
     * Sets module id.
     *
     * @param  string $modId  Module id
     * @return AMI_ModComponent
     */
    public function setModId($modId){
        $this->modId = $modId;
        return $this;
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }

    /**
     * Sets component type postfix.
     *
     * @param  string $postfix  Type postfix
     * @return AMI_ModComponent
     * @amidev Temporary
     */
    public function setPostfix($postfix){
        $this->postfix = (string)$postfix;
        return $this;
    }

    /**
     * Returns component type postfix.
     *
     * @return string
     * @amidev Temporary
     */
    public function getPostfix(){
        return $this->postfix;
    }

    /**
     * Adds sub component.
     *
     * @param  AMI_iModComponent $oComponent  Module sub component controller
     * @param  string $modId                  Module id
     * @return AMI_ModComponent
     * @amidev Temporary
     */
    public function addSubComponent(AMI_iModComponent $oComponent, $modId = null){
        $serialId = $this->serialId . '_' . sizeof($this->aSubComponents);
        $oComponent
            ->setModId(is_null($modId) ? $this->getModId() : $modId)
            ->setRootSerialId(empty($this->rootSerialId) ? $this->serialId : $this->rootSerialId)
            ->setSerialId($serialId)
            ->init();
        $this->aSubComponents[$serialId] = $oComponent;
        return $this;
    }

    /**
     * Returns serial id.
     *
     * @return string
     */
    protected function getSerialId(){
        return $this->serialId;
    }

    /**
     * Returns root serial id.
     *
     * @return string
     * @amidev Temporary
     */
    protected function getRootSerialId(){
        return $this->rootSerialId;
    }

    /**
     * Sets display view flag.
     *
     * @param bool $bDisplay  Display (true) or not (false)
     * @return void
     * @see    AMI_ModComponent::_getView()
     * @todo   Rename?
     * @amidev Temporary
     */
    protected function displayView($bDisplay = true){
        $this->bDispayView = $bDisplay;
    }

    /**
     * Returns current value of displayView.
     *
     * @return bool
     * @see    AMI_ModForm::convertRequestDates()
     * @amidev Temporary
     */
    protected function isDisplayView(){
        return $this->bDispayView;
    }

    /**
     * Returns module component view built by module id and resource id tail.
     *
     * @param  string $resIdTail  Resource id tail
     * @param  bool   $bAddResId  Add resource id flag
     * @return AMI_View
     * @amidev Temporary
     */
    protected function _getView($resIdTail, $bAddResId = true){
        if($this->bDispayView){
            foreach(array_keys($this->aSubComponents) as $index){
                $this->aSubComponents[$index]->getView();
            }
            $oView = AMI::getResource(($bAddResId ? $this->getModId() : '') . $resIdTail);
            $oView->setScope(array('_component_id' => $this->getSerialId(), '_root_component_id' => $this->getRootSerialId()));
            $oView->setModel($this->getModel());
            if(is_callable(array($oView, 'setType'))){
                $oView->setType($this->getType());
            }
            $oView->init();
        }else{
            $oView = new AMI_ViewEmpty;
        }
        return $oView;
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    protected function getModId(){
        if(empty($this->modId)){
            $this->modId = AMI::getModId(get_class($this));
        }
        return $this->modId;
    }

    /**
     * Returns model.
     *
     * @return AMI_ModTable
     * @amidev Temporary
     */
    protected function getModel(){
        if(empty($this->oModel)){
            $this->oModel = $this->initModel();
        }
        return $this->oModel;
    }

    /**
     * Initializes model.
     *
     * @return AMI_ModTable|null
     * @since  5.14.6
     */
    protected function initModel(){
        return $this->useModel ? AMI::getResourceModel($this->getModId() . '/table') : null;
    }
}

/**
 * Stub module component.
 *
 * @package ModuleComponent
 * @amidev  Temporary
 * @todo    Describe
 */
class AMI_ModComponentStub extends AMI_ModComponent{
    /**
     * Initialization.
     *
     * @return AMI_ModComponentStub
     */
    public function init(){
        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'ami_stub';
    }

    /**
     * Returns component view.
     *
     * @return AMI_ViewEmpty
     */
    public function getView(){
        return new AMI_ViewEmpty;
    }
}

/**
 * Front specblock module component.
 *
 * @package ModuleComponent
 * @amidev  Temporary
 * @todo    Describe
 */
// abstract class AMI_ModComponentSpecblock extends AMI_ModComponent{
    /**
     * Returns specblock postfix.
     *
     * @return string
     */
/*
    public abstract function getPostfix();
}
*/
