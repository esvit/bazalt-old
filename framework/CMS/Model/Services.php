<?php
/**
 * Data model
 *
 * @category  DataModel
 * @package   DataModel
 * @author    DataModel Generator v1.1
 * @version   Revision: 127 
 */

/**
 * Data model for table "cms_services"
 *
 * @category  DataModel
 * @package   DataModel
 * @author    DataModel Generator v1.1
 * @version   Revision: 127 
 */
class CMS_Model_Services extends CMS_Model_Base_Services
{
    /**
     * Return active services for site
     * also return `className`, `is_active` and `component_name`
     */
    public static function getSiteServices($siteId = null, $onlyActive = true)
    {
        if ($siteId == null) {
            $siteId = CMS_Bazalt::getSiteId();
        }
        $q = ORM::select('CMS_Model_Services `s`', '`s`.*, `sc`.`config` AS `config`, `comp`.`is_active` AS `is_active`, `comp`.`name` AS `component_name`')
                ->innerJoin('CMS_Model_ServicesRefSites `sc`', array('service_id', '`s`.`id`'))
                ->innerJoin('CMS_Model_Component `comp`', array('id', '`s`.`component_id`'));
        
        if (ENABLE_MULTISITING) {
            $q->innerJoin('CMS_Model_ComponentRefSite `ref`', array('component_id', '`comp`.`id`'))
              ->where('`ref`.`site_id` = ?', intval($siteId)) // if components active for this siteId
              ->andWhere('`sc`.`site_id` = ? OR `sc`.`site_id` IS NULL', (int)$siteId); // if services present for this siteId
        }

        if ($onlyActive) {
            $q->andWhere('`comp`.`is_active` = ?', 1);
        }
        return $q->fetchAll();
    }

    /**
     * Check if service is active
     *
     * @param string $className Class name of service
     * @param int    $siteId    Site Id
     *
     * @return bool
     */
    public static function isActive($className, $siteId = null)
    {
        // можна було зробити запитом, але ж кешем хай краще буде так
        $services = self::getSiteServices($siteId, true);

        foreach ($services as $service) {
            if ($service->className == $className) {
                return true;
            }
        }
        return false;
    }

    public function isEnable($siteId = null)
    {
        $site = ($siteId) ? CMS_Model_Site::getById($siteId) : CMS_Bazalt::getSite();

        return $site->Services->has($this);
        // можна було зробити запитом, але ж кешем хай краще буде так
        $services = self::getSiteServices($siteId, true);

        foreach ($services as $service) {
            if ($service->className == $className) {
                return true;
            }
        }
        return false;
    }
}
