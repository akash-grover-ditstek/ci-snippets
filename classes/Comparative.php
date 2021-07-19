<?php

require_once(CLASSES_DIR . "User.php");
require_once(CLASSES_DIR . "Account.php");
require_once(CLASSES_DIR . "File.php");
require_once(ENUM_DIR . "FileOrigin.php");

class Comparative
{
    public $id;
    public $property_id;
    public $user_id;
    public $account_id;

    public $business_name;
    public $street_address;
    public $street_suite;
    public $city;
    public $county;
    public $state;
    public $zipcode;

    public $type;

    public $property_image_url;
    public $condition;
    public $property_class;
    public $year_built;
    public $year_remodeled;
    public $sale_price;
    public $price_square_foot;
    public $lease_rate;
    public $term;
    public $concessions;

    public $building_size;
    public $land_size;
    public $land_dimension;
    public $date_sold;
    public $no_stories;
    public $parcel_id_apn;
    public $net_operating_income;
    public $cap_rate;

    public $frontage;
    public $utilities_select;
    public $utilities_text;
    public $zoning_type;
    public $summary;

    public $map_pin_lat;
    public $map_pin_lng;
    public $map_pin_zoom;
    public $latitude;
    public $longitude;

    private $user;
    private $account;
    public $suitesParcels;
    public $zonings;

    private $last_updated;
    private $created;

    public $building_size_for_view;
    public $land_size_for_view;
    public $sale_price_for_view;
    public $price_square_foot_for_view;
    public $frontage_for_view;
    public $condition_for_view;
    public $date_sold_for_view;
    public $state_for_view;
    public $date_sold_for_input;
    public $utilities_for_view;
    public $lease_type;
    public $land_type;
    public $comp_type;
    public $land_type_custom;

    public function __construct($data = array())
    {
        $this->user = new User();
        $this->account = new Account();
        $this->suitesParcels = array();
        $this->zonings = array();

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
            if (is_array($value)) {
                foreach ($value as $field => $val) {
                    if (property_exists(get_class($this), $key)) {
                        $this->{$field} = $val;
                    }
                }
            }
        }
    }
    public function getSummary()
    {
        return $this->summary;
    }
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    public function getYearBuilt()
    {
        return $this->year_built;
    }
    public function setYearBuilt($year_built)
    {
        $this->year_built = $year_built;
    }
    public function getSuitesParcels()
    {
        return $this->suitesParcels;
    }
    public function setSuitesParcels($suitesParcels)
    {
        $this->suitesParcels = $suitesParcels;
    }
    public function getPropertyId()
    {
        return $this->property_id;
    }
    public function setPropertyId($property_id)
    {
        $this->property_id = $property_id;
    }
    public function getYearRemodeled()
    {
        return $this->year_remodeled == 0? null : $this->year_remodeled;
    }
    public function setYearRemodeled($year_remodeled)
    {
        $this->year_remodeled = $year_remodeled;
    }
    public function getUtilitiesSelect()
    {
        return $this->utilities_select;
    }
    public function setUtilitiesSelect($utilities_select)
    {
        $this->utilities_select = $utilities_select;
    }
    public function getUtilitiesText()
    {
        return $this->utilities_text;
    }
    public function setUtilitiesText($utilities_text)
    {
        $this->utilities_text = $utilities_text;
    }
    public function getLatitude()
    {
        return $this->latitude;
    }
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }
    public function getLongitude()
    {
        return $this->longitude;
    }
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }
    public function getCounty()
    {
        return $this->county;
    }
    public function setCounty($county)
    {
        $this->county = $county;
    }
    public function getUserId()
    {
        return $this->user_id;
    }
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    public function getAccountId()
    {
        return $this->account_id;
    }
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
    }
    public function getBusinessName()
    {
        return $this->business_name;
    }
    public function setBusinessName($business_name)
    {
        $this->business_name = $business_name;
    }
    public function getStreetAddress()
    {
        return $this->street_address;
    }

    public function getFormattedAddress()
    {
        $address = [];

        if ($this->street_address) {
            $address[] = $this->street_address.', ';
        }

        if ($this->city) {
            $address[] = $this->city.', ';
        }

        if ($this->state) {
            $address[] = strtoupper($this->state);
        }

        if ($this->zipcode) {
            $address[] = $this->zipcode;
        }

        return implode(" ", $address);
    }

    public function getEvalFormattedAddress()
    {
        $address = '';

        if ($this->street_address) {
            $address = $address . $this->street_address . ' | ';
        }

        if ($this->city) {
            $address = $address . $this->city . ', ';
        }

        if ($this->state) {
            $address = $address . get_state($this->state) . ' ';
        }

        if ($this->zipcode) {
            $address = $address . $this->zipcode;
        }

        return $address;
    }
    public function setStreetAddress($street_address)
    {
        $this->street_address = $street_address;
    }
    public function getStreetSuite()
    {
        return $this->street_suite;
    }
    public function setStreetSuite($street_suite)
    {
        $this->street_suite = $street_suite;
    }
    public function getCity()
    {
        return $this->city;
    }
    public function setCity($city)
    {
        $this->city = $city;
    }
    public function getState($view = false)
    {
        if ($view) {
            return strtoupper($this->state);
        }
        return $this->state;
    }
    public function setState($state)
    {
        $this->state = $state;
    }
    public function getZipcode()
    {
        return $this->zipcode;
    }
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }
    public function getPropertyImageUrl()
    {
        return $this->property_image_url;
    }
    public function setPropertyImageUrl($property_image_url)
    {
        $this->property_image_url = $property_image_url;
    }
    public function getCondition($view = false)
    {
        if ($view) {
            if (isset($this->condition)) {
                return get_conditions($this->condition);
            } else {
                return '';
            }
        } else {
            return $this->condition;
        }
    }
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }
    public function getZoningType()
    {
        return $this->zoning_type;
    }
    public function setZoningType($zoning_type)
    {
        $this->zoning_type = $zoning_type;
    }
    public function getPropertyClass($view = false)
    {
        $class = get_property_classes($this->property_class);

        if ($view && isset($class) && !is_array($class)) {
            return $class;
        }

        return $this->property_class;
    }
    public function setPropertyClass($property_class)
    {
        $this->property_class = $property_class;
    }
    public function getSalePrice($view = false)
    {
        if ($view) {
            if (isset($this->sale_price)) {
                return maskMoney($this->sale_price);
            } else {
                return '';
            }
        } else {
            return $this->sale_price;
        }
    }
    public function setSalePrice($sale_price)
    {
        $this->sale_price = $sale_price;
    }
    public function getPriceSquareFoot($view = false)
    {
      if($this->type === 'sale') {
        if($this->sale_price > 0 && $this->land_size > 0) {
            if ($view) {
                if (isset($this->sale_price)) {
                    if($this->land_dimension === 'ACRE'){
                        return maskMoney($this->sale_price / ($this->land_size*43560));
                    }else{
                        return maskMoney($this->sale_price / $this->land_size);
                    }
                } else {
                    return '';
                }
            } else {
                return number_format($this->sale_price / $this->land_size, 2, '.', '');
            }
        }      
      } else {
        return maskMoney($this->price_square_foot);
      }
      return '';
    } 
    public function setPriceSquareFoot($price_square_foot)
    {
        $this->price_square_foot = $price_square_foot;
    }
    public function getBuildingSize($view = false)
    {
        if ($view) {
            if (isset($this->building_size)) {
                return maskArea($this->building_size);
            } else {
                return '';
            }
        } else {
            return $this->building_size;
        }
    }
    public function setBuildingSize($building_size)
    {
        $this->building_size = $building_size;
    }
    public function getLandSize($view = false, $calculation = false)
    {
        if ($view) {
            if (isset($this->land_size)) {
                if($this->land_dimension === 'ACRE'){
                    return maskArea($this->land_size*43560);
                }else{
                    return maskArea($this->land_size);
                }
            } else {
                return '';
            }
        } else {
            if($calculation){
                return ($this->land_size == 0 ? 1 : $this->land_size );
            }else{
                return $this->land_size;
            }
        }
    }
    public function getServices()
    {
        if($this->utilities_select === 'other') {
            return $this->utilities_text;
        } else {
            return $this->utilities_select;
        }
    }
    
    public function setLandSize($land_size)
    {
        $this->land_size = $land_size;
    }
    
    public function getLandDimension()
    {
        return $this->land_dimension?:'SF';
    }
    
    public function getDateSold($view = false)
    {
        if ($view) {
            if (isset($this->date_sold)) {
                return formatDate('M j, Y', $this->date_sold);
            } else {
                return '';
            }
        } else {
            return $this->date_sold;
        }
    }
    public function setLandDimension($land_dimension)
    {
        $this->land_dimension = $land_dimension;
    }
    public function setDateSold($date_sold)
    {
        $this->date_sold = $date_sold;
    }
    public function getNoStories()
    {
        return $this->no_stories;
    }
    public function setNoStories($no_stories)
    {
        $this->no_stories = $no_stories;
    }
    
    public function getParcelIdApn()
    {
        return $this->parcel_id_apn;
    }
    
    public function setParcelIdApn($parcel_id_apn)
    {
        $this->parcel_id_apn = $parcel_id_apn;
    }
    
    public function getCreated()
    {
        return $this->created;
    }
    
    public function setCreated($created)
    {
        $this->created = $created;
    }
    
    public function getLat()
    {
        return $this->getLatitude();
    }
    
    public function getLng()
    {
        return $this->getLongitude();
    }
    
    public function getExhibits()
    {
        $ci =& get_instance();
        $ci->load->model('files_model');
        return $ci->files_model->findAllObjectsBy([
            'entity_type' => 'comps',
            'entity_id' => $this->id,
            'origin' => FileOrigin::EVALUATION_EXHIBITS
        ], 'order');
    }
    
    public function getCountyDetailsUrl()
    {
        if (isset($this->county_details_url)) {
            return $this->county_details_url;
        } else {
            return null;
        }
    }
    
    public function getCountyTaxUrl()
    {
        if (isset($this->county_tax_url)) {
            return $this->county_tax_url;
        } else {
            return null;
        }
    }
    public function getSubdivisionSurveyUrl()
    {
        if (isset($this->subdivision_survey_url)) {
            return $this->subdivision_survey_url;
        } else {
            return null;
        }
    }
    public function getLastUpdated()
    {
        return $this->last_updated;
    }
    public function setLastUpdated($last_updated)
    {
        $this->last_updated = $last_updated;
    }

    public function getUser()
    {
        if(!isset($this->user->id)) {
            $ci =& get_instance();
            $ci->load->model('users_model');
            $this->user = $ci->users_model->findOneObjectBy(['id' => $this->user_id],false);
        }
        return $this->user;
    }

    public function getAccount()
    {
        return $this->account;
    }
    public function getFrontage($view = true)
    {
        if ($view) {
            $res = get_frontages($this->frontage);
            if (isset($res) && !is_array($res)) {
                return $res;
            } else {
                return null;
            }
        }

        return $this->frontage;
    }
    public function setFrontage($frontage)
    {
        $this->frontage = $frontage;
    }
    
    public function getSuitesOrParcels()
    {
        return $this->suitesParcels;
    }
    
    public function getZonings($view = false)
    {
        if ($view && isset($this->zonings) && is_array($this->zonings) && !empty($this->zonings)) {
            $result = array();


            foreach ($this->zonings as $zoning) {
                if ($zoning) {
                    $result[] = get_zonings($zoning->getZone());
                }
            }

            $result = implode(', ', $result);
            $result = !empty($result) ? $result : 'N/A';

            return $result;
        } else {
            return $this->zonings;
        }
    }

    public function getSubZonings($view = false)
    {
        if ($view && isset($this->zonings) && is_array($this->zonings) && !empty($this->zonings)) {
            $result = array();
            
            foreach ($this->zonings as $zoning) {
                if ($zoning) {
                    $z = get_zonings($zoning->getZone(), true, $zoning->getSubZone());
                    if (!in_array($z, $result)) {
                        $result[] = get_zonings($zoning->getZone(), true, $zoning->getSubZone());
                    }
                }
            }

            $result = implode(', ', $result);
            $result = !empty($result) ? $result : 'N/A';

            return $result;
        } else {
            return $this->zonings;
        }
    }
    
    public function getLeaseRate()
    {
        return $this->lease_rate;
    }
    
    public function setLeaseRate($lease_rate)
    {
        $this->lease_rate = $lease_rate;
    }
    
    public function getTerm()
    {
        return $this->term;
    }
    public function setTerm($term)
    {
        $this->term = $term;
    }
    public function getConcessions()
    {
        return $this->concessions;
    }
    public function setConcessions($concessions)
    {
        $this->concessions = $concessions;
    }
    public function getMapPinLat()
    {
        return $this->map_pin_lat;
    }
    public function setMapPinLat($map_pin_lat)
    {
        $this->map_pin_lat = $map_pin_lat;
    }
    public function getMapPinLng()
    {
        return $this->map_pin_lng;
    }
    public function setMapPinLng($map_pin_lng)
    {
        $this->map_pin_lng = $map_pin_lng;
    }
    public function getMapPinZoom()
    {
        return $this->map_pin_zoom;
    }
    public function setMapPinZoom($map_pin_zoom)
    {
        $this->map_pin_zoom = $map_pin_zoom;
    }
    public function getType()
    {
        return $this->type;
    }
    public function setType($type)
    {
        $this->type = $type;
    }

    public function createProperty($name, $value){
        $this->{$name} = $value;
    }

    public function __set($name, $value)
    {

    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
    }
    
    public function setLeaseType($lease_type)
    {
        $this->lease_type = $lease_type;
    }
    
    public function getLeaseType()
    {
        return $this->lease_type;
    }

    public function getCompType()
    {
        return $this->comp_type;
    }
    
    public function setCompType($comp_type)
    {
        $this->comp_type = $comp_type;
    }
    public function getLandType($custom=null)
    {
        if (!array_key_exists($this->land_type,get_land_type()))
        {
            return "Type My Own";
        }
        return $this->land_type;
    }
    
    public function setLandType($land_type)
    {
        $this->land_type = $land_type;
        $this->land_type_custom = $land_type;
    }
}