<?php

if (!function_exists('beginTimer')) {
    function beginTimer()
    {
        return microtime(true);
    }
}

if (!function_exists('finishTimer')) {
    function finishTimer($starttime, $process = '')
    {
        $endtime = microtime(true);
        $timediff = $endtime - $starttime;
        log_message('debug', 'Time to finish ' . $process . ': ' . number_format($timediff, 8));
    }
}

if (!function_exists('today')) {
    function today()
    {
        return date("m/d/Y", strtotime('today'));
    }
}

if (!function_exists('get_states')) {

    function get_states()
    {
        return array(
            '' => '-- Select State --',
            'al' => 'Alabama',
            'ak' => 'Alaska',
            'az' => 'Arizona',
            'ar' => 'Arkansas',
            'ca' => 'California',
            'co' => 'Colorado',
            'ct' => 'Connecticut',
            'de' => 'Delaware',
            'dc' => 'District Of Columbia',
            'fl' => 'Florida',
            'ga' => 'Georgia',
            'hi' => 'Hawaii',
            'id' => 'Idaho',
            'il' => 'Illinois',
            'in' => 'Indiana',
            'ia' => 'Iowa',
            'ks' => 'Kansas',
            'ky' => 'Kentucky',
            'la' => 'Louisiana',
            'me' => 'Maine',
            'md' => 'Maryland',
            'ma' => 'Massachusetts',
            'mi' => 'Michigan',
            'mn' => 'Minnesota',
            'ms' => 'Mississippi',
            'mo' => 'Missouri',
            'mt' => 'Montana',
            'ne' => 'Nebraska',
            'nv' => 'Nevada',
            'nh' => 'New Hampshire',
            'nj' => 'New Jersey',
            'nm' => 'New Mexico',
            'ny' => 'New York',
            'nc' => 'North Carolina',
            'nd' => 'North Dakota',
            'oh' => 'Ohio',
            'ok' => 'Oklahoma',
            'or' => 'Oregon',
            'pa' => 'Pennsylvania',
            'ri' => 'Rhode Island',
            'sc' => 'South Carolina',
            'sd' => 'South Dakota',
            'tn' => 'Tennessee',
            'tx' => 'Texas',
            'ut' => 'Utah',
            'vt' => 'Vermont',
            'va' => 'Virginia',
            'wa' => 'Washington',
            'wv' => 'West Virginia',
            'wi' => 'Wisconsin',
            'wy' => 'Wyoming'
        );
    }
}

if (!function_exists('get_state')) {

    function get_state($state = null)
    {
        if (isset($state) && isset(get_states()[$state])) {
            return get_states()[$state];
        } else {
            return null;
        }
    }
}


if (!function_exists('get_dimensions')) {

    function get_dimensions($dimension = null)
    {
        $data = array(
            'SF' => 'SF',
            'ACRE' => 'Acres'
        );

        if (isset($dimension)) {
            if (isset($data[$dimension])) {
                return $data[$dimension];
            }
        }

        return $data;
    }
}
if (!function_exists('get_most_likely_owner_user')) {
    function get_most_likely_owner_user($owneruser = null,$customoption = null)
    {
        $data = array( '' => '', 'Investor' => 'Investor','Owner User' => 'Owner User',
            'Type My Own' => 'Type My Own', $customoption =>$customoption);
        if(isset($owneruser))
        {
            if(isset($data[$owneruser]))
            {
                return $data[$owneruser];
            }
        }
        return $data;
    }

}


if (!function_exists('is_number')) {

    function is_number($number)
    {
        if (isset($number) && !is_null($number) && is_numeric($number)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('is_menu_active')) {

    function is_menu_active($class)
    {
        $ci =& get_instance();
        if ($class === $ci->router->fetch_class()) {
            if ($class === 'users' && is_user_page()) {
                return '';
            }

            return 'active';
        } else {
            if ($class === 'profile') {
                if (is_user_page()) {
                    return 'active';
                }
            }
        }

        return '';
    }
}

if (!function_exists('get_brokers')) {

    function get_brokers($excludeCurrentUser = true, $account_id = null)
    {
        $ci =& get_instance();

        if ($excludeCurrentUser) {
            if (get_logged_user_role() === UserRole::ROLE_SUPER_ADMINISTRATOR) {
                if ($account_id) {
                    $brokers = $ci->users_model->findAllBy([
                        'account_id' => $account_id,
                        'id' => ['not' => get_logged_user_id()]
                    ]);
                } else {
                    $brokers = $ci->users_model->findAllBy([
                        'account_id' => get_logged_user_account(),
                        'id' => ['not' => get_logged_user_id()]
                    ]);
                }
            } else {
                $brokers = $ci->users_model->findAllBy([
                    'account_id' => get_logged_user_account(),
                    'id' => ['not' => get_logged_user_id()]
                ]);
            }
        } else {
            if (get_logged_user_role() === UserRole::ROLE_SUPER_ADMINISTRATOR) {
                if ($account_id) {
                    $brokers = $ci->users_model->findAllBy([
                        'account_id' => $account_id
                    ]);
                } else {
                    $brokers = $ci->users_model->findAllBy();
                }
            } else {
                $brokers = $ci->users_model->findAllBy([
                    'account_id' => get_logged_user_account()
                ]);
            }
        }

        usort($brokers, function ($item1, $item2) {
            if (isset($item1['first_name']) && isset($item2['first_name'])) {
                return strcmp($item1["first_name"], $item2["first_name"]);
            }
        });

        $options = [];
        if (is_array($brokers) & !empty($brokers)) {
            foreach ($brokers as $i => $broker) {
                $options[$broker['id']] = $broker['first_name'] . ' ' . $broker['last_name'];
            }
        }
        return $options;
    }
}

if (!function_exists('is_user_page')) {

    function is_user_page()
    {
        $ci =& get_instance();

        if ('users' === $ci->router->fetch_class()) {
            $method = $ci->router->fetch_method();
            if ($method === 'edit') {
                $id = $ci->uri->segment(3);
                if (is_numeric($id) && $id === $ci->session->userdata('user_id')) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (!function_exists('searchForInArray')) {

    function searchForInArray($needle, $field, $array)
    {
        foreach ($array as $key => $val) {
            if ($val[$field] == $needle) {
                return $val;
            }
        }
        return null;
    }
}

if (!function_exists('is_json')) {
    function is_json($string)
    {
        return json_validate($string);
    }
}


if (!function_exists('json_validate')) {
    function json_validate($string)
    {
        json_decode($string);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; 
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            return false;
        }

        return true;
    }
}


if (!function_exists('get_title')) {

    function get_title()
    {

        $ci =& get_instance();

        if ($ci->router->fetch_class() === "comps") {
            return 'Comps';
        } else {
            if ($ci->router->fetch_class() === "accounts") {
                return 'Account';
            } else {
                if ($ci->router->fetch_class() === "clients") {
                    return 'Client';
                } else {
                    if ($ci->router->fetch_class() === "evaluations") {
                        return "Evaluations";
                    } else {
                        if ($ci->router->fetch_class() === "payments") {
                            return 'Payment';
                        } else {
                            if ($ci->router->fetch_class() === "results") {
                                return 'Result';
                            } else {
                                if ($ci->router->fetch_class() === "users") {
                                    return 'User';
                                } else {
                                    if ($ci->router->fetch_class() === "properties") {
                                        return 'Listings';
                                    } else {
                                        if ($ci->router->fetch_class() === "settings") {
                                            return 'Settings';
                                        } else {
                                            if ($ci->router->fetch_class() === "master_properties") {
                                                return 'Master Properties';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return '';
    }
}

if (!function_exists('get_years')) {

    function get_years()
    {
        $years = array();

        $years[''] = "-- Select Built/Remodel Year -- ";

        foreach (range(date("Y"), 1800) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }
}

if (!function_exists('get_property_types')) {

    function get_property_types()
    {
        return array(
            "commercial" => 'Commercial',
            "warehouse" => 'Warehouse',
            "commercial_warehouse" => 'Commercial/Warehouse',
            "industry" => 'Industry',
            "" => 'Property Type'
        );
    }
}

if (!function_exists('get_space_type')) {

    function get_space_type($space = null)
    {
        $data = array(
            "SUITE" => 'Suite',
            "PARCEL" => 'Parcel'
        );

        if (isset($space)) {
            if (isset($data[$space])) {
                return $data[$space];
            }
        }

        return $data;
    }
}
if (!function_exists('get_property_rights')) {

    function get_property_rights($p = null)
    {
        $data = array(
            "fee_simple" => 'Fee Simple',
            "leased_fee" => 'Leased Fee',
            "leased_hold_interest" => 'Leasehold Interest'
        );

        if (isset($p)) {
            if (isset($data[$p])) {
                return $data[$p];
            }
        }

        return $data;
    }
}

if (!function_exists('formatNumber')) {

    function formatNumber($val)
    {
        if (isset($val) && is_numeric($val) && !is_null($val) && !empty($val)) {
            if (floor($val) != $val) {
                return number_format($val, 2);
            } else {
                return number_format($val);
            }
        } else {
            return number_format(0);
        }
    }
}

if (!function_exists('get_currencies')) {

    function get_currencies()
    {
        return array(
            "usd" => 'USD - US Dollar'
        );
    }
}

if (!function_exists('get_plan_intervals')) {

    function get_plan_intervals()
    {
        return array(
            "month" => 'monthly',
            "day" => 'daily',
            "year" => 'yearly',
            "week" => 'weekly',
            "3-month" => 'every 3 months',
            "6-month" => 'every 6 months'
        );
    }
}

if (!function_exists('get_eval_image_types')) {

    function get_eval_image_types($type = null)
    {
        $list = [
            'cover' => [
                'title' => 'Sub. Property',
                'orientation' => 'Horizontal',
                'page' => '1 - Cover Photo',
            ],
            'table-of-contents' => [
                'title' => 'Sub. Property 2',
                'orientation' => 'Vertical',
                'page' => '2 - Table of Contents',
            ],
            'executive-summary-details' => [
                'title' => 'Sub. Property 3',
                'orientation' => 'Vertical',
                'page' => '5 - Executive Summary',
            ],
            'property-summary-top-image' => [
                'title' => 'Sub. Property 4',
                'orientation' => 'Horizontal',
                'page' => '7 - Property Summary',
            ],
            'property-summary-bottom-image' => [
                'title' => 'Aerial (Satellite)',
                'orientation' => 'Horizontal',
                'page' => '7 - Property Summary',
            ],
            'property-summary-info-image' => [
                'title' => 'Sub. Property Panoramic 1',
                'orientation' => 'Horizontal',
                'page' => '8 - Area Info',
            ],
            'property-summary-map-aerials-image' => [
                'title' => 'Aerial (Bird View)',
                'orientation' => 'Horizontal',
                'page' => '9 - Summary',
            ],
            'property-details-image' => [
                'title' => 'Sub. Property Panoramic 2',
                'orientation' => 'Horizontal',
                'page' => 10,
            ],
            'sub-property-panoramic' => [
                'title' => 'Sub. Property Panoramic 3',
                'orientation' => 'Horizontal',
                'page' => 12,
            ],
            'sub-property-1' => [
                'title' => 'Sub. Property 5',
                'orientation' => 'Horizontal',
                'page' => 17,
            ],
            'sub-property-2' => [
                'title' => 'Sub. Property 6',
                'orientation' => 'Horizontal',
                'page' => 17,
            ],
            'sub-property-3' => [
                'title' => 'Sub. Property 7',
                'orientation' => 'Horizontal',
                'page' => 17,
            ],
            'extra-image' => [
                'title' => 'Additional image',
                'description' => 'Extra image uploaded by user'
            ],
            'sub-property-panoramic-bottom' => [
                'title' => 'Sub. Property Panoramic 4',
                'orientation' => 'Horizontal',
                'page' => 18,
            ]
        ];

        if (isset($type)) {
            if (isset($list[$type])) {
                return $list[$type];
            }
        }
        return $list;
    }
}

if (!function_exists('get_listing_types')) {

    function get_listing_types($listing = null)
    {
        $listings = array(
            "sale" => 'Sale',
            "lease" => 'Lease'
        );

        if (isset($listing) && isset($listings[$listing])) {
            return $listings[$listing];
        }

        return $listings;
    }
}

if (!function_exists('get_evaluation_type')) {

    function get_evaluation_type($listing = null)
    {
        $listings = array(
            "sale" => 'Sale',
            "non_sale" => 'Non Sale'
        );

        if (isset($listing) && isset($listings[$listing])) {
            return $listings[$listing];
        }

        return $listings;
    }
}

if (!function_exists('get_comp_types')) {

    function get_comp_types($listing = null)
    {
        $listings = array(
            "sale" => 'Sale',
            "lease" => 'Lease'
        );

        if (isset($listing) && isset($listings[$listing])) {
            return $listings[$listing];
        }

        return $listings;
    }
}

if (!function_exists('get_payment_frequency')) {

    function get_payment_frequency($listing = null)
    {
        $listings = array(
            "sf_yr" => '$/SF/Yr',
            "monthly" => 'Monthly',
            "sf_mo" => '$/SF/Mo',
            "annually" => 'Annual Rent'
        );

        if (isset($listing) && isset($listings[$listing])) {
            return $listings[$listing];
        }

        return $listings;
    }
}

if (!function_exists('get_lease_rate_units')) {

    function get_lease_rate_units($listing = null, $send_list = false)
    {
        $listings = array(
            "sf_yr" => '/SF/Yr',
            "monthly" => '/Mo',
            "sf_mo" => '/SF/Mo',
            "annually" => '/Yr',
            "dollars_per_acre_per_month" => '/Acre/Mo',
            "dollars_per_acre_per_year" => '/Acre/Yr',
        );

        if (isset($listing) && isset($listings[$listing])) {
            return $listings[$listing];
        }

        if ($send_list) {
            return $listings;
        } else {
            return '';
        }
    }
}

if (!function_exists('map_frequency')) {

    function map_frequency($code = null)
    {
        switch ($code) {
            case 'sf_yr':
                return "SF/Yr";
            case 'dollars_per_acre_per_month':
                return "Acre/Mo";
            case 'dollars_per_acre_per_year':
                return "Acre/Yr";
            case 'annually':
                return "Annually";
            case 'sf_mo':
                return "SF/Mo";
            case 'monthly':
                return "Monthly";
            default:
                return false;
        }
    }
}

if (!function_exists('get_lease_types')) {

    function get_lease_types($lease_type = null)
    {
        $data = array(
            '' => '-- Select Lease Type --',
            'absolute_net' => 'Absolute Net',
            'gross' => 'Gross',
            'modified_gross' => 'Modified Gross',
            'nnn' => 'NNN',
            'modified_net' => 'Modified Net',
            'full_services' => 'Full Service',
            'ground_lease' => 'Ground Lease'
        );

        if (isset($lease_type)) {
            if (isset($data[$lease_type])) {
                return $data[$lease_type];
            }
        }

        return $data;
    }
}

if (!function_exists('get_deal_status')) {

    function get_deal_status($status = null)
    {
        $data = array(
            'available' => 'Available',
            'off_market' => 'Off Market',
            'closed' => 'Closed',
            'under_contract' => 'Under Contract'
        );

        if (isset($status)) {
            if (isset($data[$status])) {
                return $data[$status];
            }
        }

        return $data;
    }
}

if (!function_exists('get_property_classes')) {

    function get_property_classes($class = null)
    {
        $classes = array(
            "" => '-- Select Property Class --',
            "class_a" => 'Class A',
            "class_b" => 'Class B',
            "class_c" => 'Class C',
            "class_d" => 'Class D'
        );

        if (isset($class) && isset($classes[$class])) {
            return $classes[$class];
        }

        return $classes;
    }
}

if (!function_exists('get_property_status')) {

    function get_property_status($status = null)
    {
        $classes = array(
            "AVAILABLE" => 'Available',
            "OFF_MARKET" => 'Off Market',
            "CLOSED" => 'Closed',
            "UNDER_CONTRACT" => 'Under Contract'
        );

        if (isset($status) && isset($classes[$status])) {
            return $classes[$status];
        }

        return $classes;
    }
}

if (!function_exists('get_property_type')) {

    function get_property_type($value)
    {
        return get_property_types()[$value];
    }
}


if (!function_exists('contains')) {

    function contains($str, $needle)
    {
        return strpos($str, $needle) !== false;
    }
}

if (!function_exists('get_situations')) {

    function get_situations()
    {
        return array(
            "excellent" => 'Excellent',
            "very_good" => 'Very Good',
            "good" => 'Good',
            "average" => 'Average',
            "poor" => 'Poor',
            "" => 'Zoning Type'
        );
    }
}

if (!function_exists('get_topographies')) {

    function get_topographies()
    {
        return array(
            "level" => 'Level',
            "sloped" => 'Sloped',
            "very_sloped" => 'Very Sloped'
        );
    }
}

if (!function_exists('get_topography')) {

    function get_topography($topography = null)
    {
        if (isset(get_topographies()[$topography])) {
            return get_topographies()[$topography];
        } else {
            return null;
        }
    }
}

if (!function_exists('get_buildout_property_type')) {

    function get_buildout_property_type($id)
    {
        $zonings = array(
            1 => 'office',
            2 => 'retail',
            3 => 'industrial',
            5 => 'land',
            6 => 'multi_family',
            7 => 'special',
            8 => 'hospitality',
            9=>'single_family'
        );

        if (isset($zonings[$id])) {
            return $zonings[$id];
        } else {
            return false;
        }
    }
}

if (!function_exists('get_buildout_lease_type')) {

    function get_buildout_lease_type($id)
    {
        $types = array(
            1 => 'gross',
            2 => 'modified_gross',
            3 => 'nnn',
            4 => 'modified_net',
            5 => 'full_services',
            6 => 'ground_lease'
        );

        if (isset($types[$id])) {
            return $types[$id];
        } else {
            return false;
        }
    }
}
if (!function_exists('get_conforming_using_determinations')) {

    function get_conforming_using_determinations($type = null)
    {
        $types = array(
            'Appears to be conforming' => 'Appears to be conforming',
            'Appears to be non-conforming' => 'Appears to be non-conforming',
            'Appears to be conforming pending special review' => 'Appears to be conforming pending special review'
        );

        if ($type) {
            if (isset($types[$type])) {
                return $types[$type];
            } else {
                return false;
            }
        }

        return $types;
    }
}

if (!function_exists('get_compliances')) {
    function get_compliances($compliant = null)
    {
        $data = ['compliant' => 'Appears to be compliant', 'non_compliant' => 'Appears to be non-compliant'];

        if (isset($compliant)) {
            if ($data[$compliant]) {
                return $data[$compliant];
            } else {
                return null;
            }
        }

        return $data;
    }
}

if (!function_exists('get_types')) {
    function get_types($type = null)
    {
        $types = array(
            null => 'Select category',
            'office_and_industrial' => 'Office and Industrial',
            'representation' => 'Tenant/Buyer Representation'
        );

        if (isset($type) && isset($types[$type])) {
            return $types[$type];
        }

        return $types;
    }
}

if (!function_exists('get_buildout_property_subtype')) {

    function get_buildout_property_subtype($id, $subid)
    {
        $zones = array(
            1 => array(
                101 => 'office_building',
                102 => 'creative_loft',
                103 => 'executive_suites',
                104 => 'medical',
                105 => 'institutional',
                106 => 'office_warehouse'
            ),
            2 => array(
                201 => 'street_retail',
                202 => 'strip_center',
                203 => 'free_standing',
                204 => 'regional_mall',
                205 => 'retail_pad',
                206 => 'vehicle_related',
                207 => 'outlet_center',
                208 => 'power_center',
                209 => 'neighborhood',
                210 => 'community',
                211 => 'specialty',
                212 => 'festival_center',
                213 => 'restaurant',
                214 => 'post_office'
            ),
            3 => array(
                301 => 'manufacturing',
                302 => 'distribution',
                303 => 'flex_space',
                304 => 'research',
                305 => 'cold_storage',
                306 => 'office_showroom',
                307 => 'truck_terminal',
                308 => 'self_storage',
            ),
            5 => array(
                501 => 'office',
                502 => 'retail',
                503 => 'retail_pad',
                504 => 'industrial',
                505 => 'residential',
                506 => 'multi_family',
                507 => 'other'
            ),
            6 => array(
                601 => 'high_rise',
                602 => 'mid_rise',
                603 => 'low_rise',
                604 => 'government',
                605 => 'mobile_park',
                606 => 'senior_living',
                607 => 'skilled_nursing',
            ),
            8 => array(
                801 => 'full_service',
                802 => 'limited_service',
                803 => 'select_service',
                804 => 'resort',
                805 => 'economy',
                806 => 'extended_stay',
                807 => 'casino'
            ),
            7 => array(
                701 => 'school',
                702 => 'marina',
                703 => 'other',
                704 => 'golf_course',
                705 => 'church'
            ),
            9 => array(
                901 =>'economy_class',
                902 =>'luxury_class',
                903 =>'luxury_class',
                904 =>'townhouse',
                905 =>'condominium',
            )
        );

        if (isset($zones[$id]) && isset($zones[$id][$subid])) {
            return $zones[$id][$subid];
        } else {
            return false;
        }
    }
}

if (!function_exists('get_zonings')) {

    function get_zonings($zoning = null, $include_children = false, $subzoning = null, $add_empty_value = false, $show_all = null)
    {
        $zonings = array(
            '' => '-- Select Property Type --',
            'office' => 'Office',
            'retail' => 'Retail',
            'industrial' => 'Industrial',
            'multi_family' => 'Multi-Family',
            'hospitality' => 'Hospitality',
            'special' => 'Special',
            'single_family' => 'Single Family Residence',
        );

        if ($add_empty_value) {
            $zonings[''] = '-- Select Property Type --';
        }

        if ($show_all) {
            $zonings[''] = 'Show All';
        }

        if ($include_children) {

            $children = array(
                'office' => array(
                    '' => '-- Select a Subtype --',
                    'office_building' => 'Office Building',
                    'creative_loft' => 'Creative Loft',
                    'executive_suites' => 'Executive Suites',
                    'medical' => 'Medical',
                    'institutional' => 'Institutional',
                    'office_warehouse' => 'Office Warehouse',
                    'Type My Own'=>'Type My Own'
                ),
                'retail' => array(
                    '' => '-- Select a Subtype --',
                    'street_retail' => 'Street Retail',
                    'strip_center' => 'Strip Center',
                    'free_standing' => 'Free Standing Building',
                    'regional_mall' => 'Regional Mall',
                    'retail_pad' => 'Retail Pad',
                    'vehicle_related' => 'Vehicle Related',
                    'outlet_center' => 'Outlet Center',
                    'power_center' => 'Power Center',
                    'neighborhood' => 'Neighborhood Center',
                    'community' => 'Community Center',
                    'specialty' => 'Specialty Center',
                    'festival_center' => 'Theme Festival Center',
                    'restaurant' => 'Restaurant',
                    'post_office' => 'Post Office',
                    'Type My Own'=>'Type My Own'
                ),
                'industrial' => array(
                    '' => '-- Select a Subtype --',
                    'manufacturing' => 'Manufacturing',
                    'distribution' => 'Distribution',
                    'flex_space' => 'Flex Space',
                    'research' => 'Research and Development',
                    'cold_storage' => 'Refrigerated/ Cold Storage',
                    'truck_terminal' => 'Truck Terminal/Hub/Transit',
                    'self_storage' => 'Self-Storage',
                    'other' => 'Other',
                    'Type My Own'=>'Type My Own'
                ),
                'land' => array(
                    '' => '-- Select a Subtype --',
                    'office' => 'Office',
                    'retail' => 'Retail',
                    'retail_pad' => 'Retail Pad',
                    'industrial' => 'Industrial',
                    'residential' => 'Residential',
                    'multi_family' => 'Multifamily',
                    'other' => 'Other',
                    'ag' => 'Agricultural',
                    'Type My Own'=>'Type My Own'
                ),
                'multi_family' => array(
                    '' => '-- Select a Subtype --',
                    'high_rise' => 'High-Rise',
                    'mid_rise' => 'Mid-Rise',
                    'low_rise' => 'Low-Rise/Garden',
                    'government' => 'Government Subsidized',
                    'mobile_park' => 'Mobile Home Park',
                    'senior_living' => 'Senior Living',
                    'skilled_nursing' => 'Skilled Nursing',
                    'residential' => 'Residential',
                    'Type My Own'=>'Type My Own'
                ),
                'hospitality' => array(
                    '' => '-- Select a Subtype --',
                    'full_service' => 'Full Service',
                    'limited_service' => 'Limited Service',
                    'select_service' => 'Select Service',
                    'resort' => 'Resort',
                    'economy' => 'Economy',
                    'extended_stay' => 'Extended Stay',
                    'casino' => 'Casino',
                    'Type My Own'=>'Type My Own'
                ),
                'special' => array(
                    '' => '-- Select a Subtype --',
                    'school' => 'School',
                    'marina' => 'Marina',
                    'golf_course' => 'Golf Course',
                    'church' => 'Church',
                    'other' => 'Other',
                    'Type My Own'=>'Type My Own'
                ),
                'single_family' => array(
                    '' => '-- Select a Subtype --',
                    'economy_class' =>'Economy Class',
                    'average_class' =>'Average Class',
                    'luxury_class' =>'Luxury Class',
                    'townhouse' =>'Townhouse',
                    'condominium' =>'Condominium',
                    'Type My Own'=>'Type My Own'
                )
            );

            $newZonings = array();

            foreach ($zonings as $k => $zone) {
                if ($k !== '') {
                    $newZonings[$k] = array(
                        'name' => $zone
                    );

                    if (isset($children[$k])) {
                        $newZonings[$k]['children'] = $children[$k];
                    }
                }
            }
            $zonings = $newZonings;
        }

        if (isset($zoning)) {
            if ($include_children && isset($subzoning)) {
                if (isset($zonings[$zoning]['children'][$subzoning])) {
                    return $zonings[$zoning]['children'][$subzoning];
                } else {
                    return null;
                }
            } else {
                if (isset($zonings[$zoning])) {
                    return $zonings[$zoning];
                } else {
                    return null;
                }
            }
        }

        return $zonings;
    }
}

if (!function_exists('get_zone')) {
    function get_zone($zoning = null)
    {
        $zonings = array(
            'office' => 'Office',
            'retail' => 'Retail',
            'industrial' => 'Industrial',
            'land' => 'Land',
            'multi_family' => 'Multi-Family',
            'hospitality' => 'Hospitality',
            'special' => 'Special',
            'single_family'=>'Single Family Residence'
        );

        if (isset($zonings[$zoning])) {
            return $zonings[$zoning];
        }

        return null;
    }
}

if (!function_exists('get_subzone')) {
    function get_subzone($subzone = null,$allsubzone = null)
    {
        $subzones = array(
            'office_building' => 'Office Building',
            'creative_loft' => 'Creative Loft',
            'executive_suites' => 'Executive Suites',
            'medical' => 'Medical',
            'institutional' => 'Institutional',
            'office_warehouse' => 'Office Warehouse',
            'street_retail' => 'Street Retail',
            'strip_center' => 'Strip Center',
            'free_standing' => 'Free Standing Building',
            'regional_mall' => 'Regional Mall',
            'retail_pad' => 'Retail Pad',
            'vehicle_related' => 'Vehicle Related',
            'outlet_center' => 'Outlet Center',
            'power_center' => 'Power Center',
            'neighborhood' => 'Neighborhood Center',
            'community' => 'Community Center',
            'specialty' => 'Specialty Center',
            'festival_center' => 'Theme Festival Center',
            'restaurant' => 'Restaurant',
            'post_office' => 'Post Office',
            'manufacturing' => 'Manufacturing',
            'distribution' => 'Distribution',
            'flex_space' => 'Flex Space',
            'research' => 'Research and Development',
            'cold_storage' => 'Refrigerated/ Cold Storage',
            'office_showroom' => 'Office Showroom',
            'truck_terminal' => 'Truck Terminal/Hub/Transit',
            'self_storage' => 'Self-Storage',
            'aerospace' => 'Aerospace',
            'office' => 'Office',
            'retail' => 'Retail',
            'industrial' => 'Industrial',
            'residential' => 'Residential',
            'multi_family' => 'Multifamily',
            'other' => 'Other',
            'ag' => 'Ag',
            'high_rise' => 'High-Rise',
            'mid_rise' => 'Mid-Rise',
            'low_rise' => 'Low-Rise/Garden',
            'government' => 'Government Subsidized',
            'mobile_park' => 'Mobile Home Park',
            'senior_living' => 'Senior Living',
            'skilled_nursing' => 'Skilled Nursing',
            'full_service' => 'Full Service',
            'limited_service' => 'Limited Service',
            'select_service' => 'Select Service',
            'resort' => 'Resort',
            'economy' => 'Economy',
            'extended_stay' => 'Extended Stay',
            'casino' => 'Casino',
            'school' => 'School',
            'marina' => 'Marina',
            'golf_course' => 'Golf Course',
            'church' => 'Church',
            'economy_class' =>'Economy Class',
            'average_class' =>'Average Class',
            'luxury_class' =>'Luxury Class',
            'townhouse' =>'Townhouse',
            'condominium' =>'Condominium',
        );

        if (isset($subzones[$subzone])) {
            return $subzones[$subzone];
        }

        if (isset($allsubzone)) {
            return $subzones;
        }

        return null;
    }
}

if (!function_exists('get_percentages_for_approaches')) {

    function get_percentages_for_approaches()
    {
        $data = [];
        for ($i = 20; $i >= -20; $i = $i - 2.50) {
            $data[(string)$i] = $i . '%';
        }
        return $data;
    }
}

if (!function_exists('get_percentages')) {

    function get_percentages()
    {
        $data = [];
        for ($i = 0; $i <= 100; $i = $i + 5) {
            $data[(string)$i] = $i . '%';
        }
        return $data;
    }
}

if (!function_exists('get_heights')) {

    function get_heights()
    {
        return array(
            "height_1" => 'Height 1',
            "height_2" => 'Height 2',
            "height_3" => 'Height 3',
            "height_4" => 'Height 4',
            "height_5" => 'Height 5',
            "" => 'Height'
        );
    }
}


if (!function_exists('get_conditions')) {

    function get_conditions($condition = null)
    {
        $conditions = array(
            "" => '-- Select Property Condition --',
            "excellent" => 'Excellent',
            "very_good" => 'Very Good',
            "good" => 'Good',
            "average" => 'Average',
            "fair" => 'Fair',
            "poor" => 'Poor'
        );

        if (isset($condition) && isset($conditions[$condition])) {
            return $conditions[$condition];
        }

        return $conditions;
    }
}

if (!function_exists('get_frontages')) {

    function get_frontages($frontage = null)
    {
        $data = array(
            "poor" => 'Poor',
            "fair" => 'Fair',
            "average" => 'Average',
            "good" => 'Good',
            "very_good" => 'Very Good',
            "excellent" => 'Excellent'
        );;
        if (isset($frontage)) {
            if (isset($data[$frontage])) {
                return $data[$frontage];
            }
        }

        return $data;
    }
}

if (!function_exists('get_utilities')) {

    function get_utilities($utility = null)
    {
        $data = array(
            "Full City Services" => "Full City Services",
            "Septic and Cistern" => "Septic and Cistern",
            "Septic and Well" => "Septic and Well",
            "Community Sewer and Water" => "Community Sewer and Water",
            "No City Services" => "No City Services",
            "other" => 'Type My Own'
        );

        if (isset($utility)) {
            if (isset($data[$utility])) {
                return $data[$utility];
            }
        }

        return $data;
    }
}

if (!function_exists('get_categories')) {

    function get_categories()
    {
        return array(
            null => 'Category',
            'small' => 'Small Property',
            'med' => 'Medium Property',
            'large' => 'Large Property',
            'xlarge' => 'Extra Large Property',
        );
    }
}

if (!function_exists('get_situation')) {

    function get_situation($value)
    {
        return get_situations()[$value];
    }
}

if (!function_exists('unmask')) {

    function unmask($value)
    {
        return floatval(preg_replace('/[^\d.]/', '', $value));
    }
}

if (!function_exists('trim_trailing_zeroes')) {

    function trim_trailing_zeroes($nbr)
    {
        return strpos($nbr, '.') !== false ? rtrim(rtrim($nbr, '0'), '.') : $nbr;
    }
}


if (!function_exists('formatDateForDatabase')) {

    function formatDateForDatabase($value)
    {
        return date("Y-m-d H:i:s", strtotime($value));
    }
}

if (!function_exists('formatDate')) {

    function formatDate($format, $value)
    {
        return date($format, strtotime($value));
    }
}

if (!function_exists('maskMoney')) {

    function maskMoney($value, $decimals = 2)
    {
        $result = number_formatter($value, $decimals);

        if (strpos($result, '.00') !== false) {
            $result = str_replace('.00', '', $result);
        }

        return $result;
    }
}

if (!function_exists('maskPercentage')) {

    function maskPercentage($value, $decimals = 2)
    {
        $result = number_formatter($value, $decimals,false);

        if (strpos($result, '.00') !== false) {
            $result = str_replace('.00', '', $result);
        }
        return $result."%";
    }
}

if (!function_exists('get_settings')) {

    function get_settings($setting = null)
    {
        $data = array(
            null => 'Selection settings',
            'engagement_letter_content' => 'Engagement Letter - Evaluation',
            'market_survey' => 'Market Survey'
        );

        if (isset($setting) && isset($data[$setting])) {
            return $data[$setting];
        }

        return $data;
    }
}

if (!function_exists('get_settings_type')) {

    function get_settings_type()
    {
        return array(
            null => '',
            'engagement_letter_content' => SettingsType::EVALUATION_REPORTS
        );
    }
}

if (!function_exists('maskArea')) {

    function maskArea($value, $dimension = "SF", $decimals = 0, $format = true)
    {
        if ($format) {
            return number_format($value, $decimals, '.', ',') . " " . $dimension;
        } else {
            return $value . " " . $dimension;
        }
    }
}

if (!function_exists('getLandDimension')) {

    function getLandDimension($dimension)
    {
        $dimensions = array(
            'SF' => 'SF',
            'ACRE' => 'Acres'
        );

        if (isset($dimensions[$dimension])) {
            return $dimensions[$dimension];
        }

        return '';
    }
}

if (!function_exists('maskAreaNumber')) {

    function maskAreaNumber($value)
    {
        $result = number_format($value, 2, '.', ',');

        if (strpos($result, '.00') !== false) {
            $result = str_replace('.00', '', $result);
        }

        return $result;
    }
}

if (!function_exists('base64url_encode')) {

    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {

    function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}

if (!function_exists('makeDir')) {

    function makeDir($path)
    {
        return is_dir($path) || mkdir($path);
    }
}

if (!function_exists('adddress_to_string')) {
    function adddress_to_string($data = array())
    {
        $string = array();

        foreach ($data as $key => $info) {
            if (isset($info)) {

                if ($key === 'state') {
                    $info = strtoupper($info);
                }

                array_push($string, $info);
            }
        }

        return implode(", ", $string);
    }
}

if (!function_exists('get_input_value')) {

    function get_input_value($data, $attribute, $default = null, $multiplier = null)
    {
        if (isset($data[$attribute])) {
            if ($multiplier && (is_numeric($multiplier) && is_numeric($data[$attribute]))) {
                return $multiplier * $data[$attribute];
            } else {
                return $data[$attribute];
            }
        } else {
            return $default;
        }
    }
}

if (!function_exists('get_overview_input_value')) {

    function get_overview_input_value($data, $area, $attribute)
    {
        if (isset($data['overview'][$area][$attribute])) {
            return $data['overview'][$area][$attribute];
        } else {
            return null;
        }
    }
}

if (!function_exists('get_indefinite_article')) {

    function get_indefinite_article($word = null)
    {
        $an_exceptions = ['euro', 'europe', 'european', 'eucalyptus', 'eulogy', 'uranium', 'urinal', 'urologist', 'unicorn', 'uniform', 'unit', 'university', 'user'];

        $a_exceptions = ['warehouse', 'helicopter', 'house', 'human'];

        $word = strtolower($word);
        if (isset($word) && isset($word[0])) {
            if (in_array($word[0], ['a', 'e', 'i', 'o', 'u'])) {
                if (in_array($word, $an_exceptions)) {
                    return 'a';
                } else {
                    return 'an';
                }
            } else {
                return 'a';
            }
        } else {
            return 'a';
        }
    }
}

if (!function_exists('get_income_approach_input_value')) {

    function get_income_approach_input_value($data, $area, $attribute)
    {
        if (isset($area)) {
            if (isset($data['income_approach'][$area][$attribute])) {
                return $data['income_approach'][$area][$attribute];
            }
        } else {
            if (isset($data['income_approach'][$attribute])) {
                return $data['income_approach'][$attribute];
            }
        }

        return null;
    }
}

if (!function_exists('get_income_approach_input_value')) {

    function get_income_approach_input_value($data, $area, $attribute)
    {
        if (isset($area)) {
            if (isset($data['income_approach'][$area][$attribute])) {
                return $data['income_approach'][$area][$attribute];
            }
        } else {
            if (isset($data['income_approach'][$attribute])) {
                return $data['income_approach'][$attribute];
            }
        }

        return null;
    }
}

if (!function_exists('get_approach_checkbox_value')) {

    function get_approach_checkbox_value($data, $attribute)
    {
        if (isset($data['overview']['approaches'][$attribute])) {
            return 'checked';
        } else {
            return null;
        }
    }
}

if (!function_exists('sum_up_array')) {

    function sum_up_array($numbers = [])
    {
        $sum = 0.00;

        if (is_array($numbers)) {
            foreach ($numbers as $number) {
                if (is_numeric($number)) {
                    $sum += floatval($number);
                }
            }
        }

        return $sum;
    }
}

if (!function_exists('evaluation_url')) {
    function evaluation_url($step, $id = null)
    {
        $ci =& get_instance();
        $client = $ci->input->get('client');

        if (isset($id)) {
            return base_url('evaluations/' . $step . '/' . $id);
        } else {
            if (isset($client)) {
                return base_url('evaluations/' . $step . '?client=' . $client);
            } else {
                return base_url('evaluations/' . $step);
            }
        }
    }
}

if (!function_exists('get_nav_items')) {
    function get_nav_items()
    {
        $ci =& get_instance();
        $ci->load->model('cms_menus_model');
        $ci->load->model('cms_menu_items_model');

        $menu = $ci->cms_menus_model->findBy(['type' => 'MLS_FOOTER']);

        if (isset($menu['id'])) {
            $items = $ci->cms_menu_items_model->findAllObjectsBy(['menu_id' => $menu['id']]);

            if (!empty($items)) {
                return $items;
            }
        }

        return array();
    }
}

if (!function_exists('get_menu_items')) {
    function get_menu_items()
    {
        $ci =& get_instance();
        $ci->load->model('cms_menus_model');
        $ci->load->model('cms_menu_items_model');

        $menu = $ci->cms_menus_model->findBy(['type' => 'MLS_MENU']);

        if (isset($menu['id'])) {
            $items = $ci->cms_menu_items_model->findAllObjectsBy(['menu_id' => $menu['id']]);

            if (!empty($items)) {
                return $items;
            }
        }

        return array();
    }
}

if (!function_exists('cleanFileUrl')) {
    function cleanFileUrl($url)
    {
        $broken_url = explode('/', $url);
        if (isset($broken_url) && $broken_url > 0) {
            $fileName = $broken_url[count($broken_url) - 1];
            if (isset($fileName) && !empty($fileName)) {
                $brokenFileName = explode('_', $fileName);
                if (isset($brokenFileName) && is_array($brokenFileName) && count($brokenFileName) > 0) {
                    $extension = strstr($brokenFileName[count($brokenFileName) - 1], '.');
                    $fileNameWithNoHash = implode("_", array_slice($brokenFileName, 0, count($brokenFileName) - 1));
                    if (isset($extension) && isset($fileNameWithNoHash) && !empty($extension) && !empty($fileNameWithNoHash)) {
                        return $fileNameWithNoHash . $extension;
                    }
                }
            }
        }
        return $url;
    }
}

if (!function_exists('getHtmlText')) {
    function getHtmlText($text = '', $tag = 'p')
    {
        $es = array();

        preg_match_all("#<\s*?$tag\b[^>]*>(.*?)</$tag\b[^>]*>#s", $text, $elements);

        if (isset($elements[0])) {
            foreach ($elements[0] as $element) {
                $elWithNoTags = strip_tags($element);
                if (!empty($elWithNoTags)) {
                    $es[] = $elWithNoTags;
                }
            }
        }

        return $es;
    }
}

if (!function_exists('getDefaultValue')) {
    function getDefaultValue()
    {
        return 'N/A';
    }
}

if (!function_exists('search_in_array')) {
    function search_in_array($value, $array, $key)
    {
        foreach ($array as $item) {
            if ($item[$key] == $value) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('filter_sensitive_data')) {
    function filter_sensitive_data($array = array())
    {
        return array_filter($array, function ($k) {
            return !(strpos($k, 'password') !== false) && !(strpos($k, 'ssn') !== false);
        }, ARRAY_FILTER_USE_KEY);
    }
}

function mat_select($label, $data = '', $options = array(), $selected = array(), $extra = '')
{
    return form_dropdown($data, $options, $selected, $extra) . "<label>{$label}</label>";
}


if (!function_exists('trunc_notes')) {

    function trunc_notes($notes)
    {
        $limit = 400;

        if (strpos($notes, "<li>") !== false) {

            $lis = str_replace("</li>", "", explode('<li>', $notes));

            $items = array();
            $wordCounter = 1;

            foreach ($lis as $i => $item) {
                if (!empty($item)) {
                    if ($wordCounter <= 5) {
                        $items[] = $item;
                        $wordCounter++;
                    }
                }
            }

            $result = array();

            foreach ($items as $j => $item) {
                $result[] = '<li>' . $item . '</li>';
            }

            return implode("", $result);
        } elseif (strlen($notes) > $limit) {
            $paragraphs = explode('.', $notes);

            $items = array();
            $wordCounter = 0;

            foreach ($paragraphs as $i => $item) {

                if (!empty($item)) {
                    $wordCounter += intval(strlen($item));

                    if ($wordCounter <= $limit) {
                        $items[] = $item;
                    }
                }
            }

            $result = array();

            foreach ($items as $j => $item) {
                $result[] = $item . '.';
            }

            return implode("", $result);
        } else {
            return $notes;
        }
    }
}

if (!function_exists('cdn_url')) {
    function cdn_url($url = null)
    {
        if ($url) {
            return config_item('s3_base_url') . $url;
        } else {
            return config_item('s3_base_url');
        }
    }
}

if (!function_exists('get_autocomplete')) {
    function get_autocomplete()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false
            || strpos($_SERVER['HTTP_USER_AGENT'], 'CriOS') !== false) {
            return "new-password";
        }

        return "off";
    }
}

if (!function_exists('get_url')) {
    function get_url($url = null)
    {
        if (strpos($url, 'upload/') !== false) {
            return base_url($url);
        } else {
            return cdn_url($url);
        }
    }
}

if (!function_exists('depreciation_table')) {
    function depreciation_table()
    {
        $html = '<table id=\'depreciation-table-tooltip\'>
                    <thead>
                        <tr> 
                           <th colspan=\'4\'>Building Material</th>
                        </tr>  
                        <tr> 
                           <th>Observed Age (Years)</th>
                           <th>Frame</th>
                           <th>Masonry On Wood</th>
                           <th>Masonry On Masonry Steel</th>
                        </tr>
                    </thead>';

        $rows = [
            [1, 1, 0, 0],
            [2, 2, 1, 0],
            [3, 3, 2, 1],
            [4, 4, 3, 2],
            [5, 6, 5, 3],
            [10, 20, 15, 8],
            [15, 25, 20, 15],
            [20, 30, 25, 20],
            [25, 35, 30, 25],
            [30, 40, 35, 30],
            [35, 45, 40, 35],
            [40, 50, 45, 40],
            [45, 55, 50, 45],
            [50, 60, 55, 50],
            [55, 65, 60, 55],
            [60, 70, 65, 60]
        ];

        $html .= "<tbody>";
        foreach ($rows as $i => $row) {
            if ($i === 0) {
                $html .=
                    "<tr>
                        <td>{$row[0]}</td>
                        <td>{$row[1]}%</td>
                        <td>{$row[2]}%</td>
                        <td>{$row[3]}%</td>
                    </tr>";
            } else {
                $html .=
                    "<tr>
                        <td>{$row[0]}</td>
                        <td>{$row[1]}</td>
                        <td>{$row[2]}</td>
                        <td>{$row[3]}</td>
                    </tr>";
            }
        }
        $html .= "</tbody>";

        $html .= "</table>";

        return $html;
    }
}

if (!function_exists('get_icons')) {
    function get_icons($icon = null)
    {
        $data = array(
            null => "Select an icon",
            "<i class='material-icons'>3d_rotation</i>" => "3d rotation",
            "<i class='material-icons'>ac_unit</i>" => "ac unit",
            "<i class='material-icons'>access_alarm</i>" => "access alarm",
            "<i class='material-icons'>access_alarms</i>" => "access alarms",
            "<i class='material-icons'>access_time</i>" => "access time",
            "<i class='material-icons'>accessibility</i>" => "accessibility",
            "<i class='material-icons'>accessible</i>" => "accessible",
            "<i class='material-icons'>account_balance</i>" => "account balance",
            "<i class='material-icons'>account_balance_wallet</i>" => "account balance wallet",
            "<i class='material-icons'>account_box</i>" => "account box",
            "<i class='material-icons'>account_circle</i>" => "account circle",
            "<i class='material-icons'>adb</i>" => "adb",
            "<i class='material-icons'>add</i>" => "add",
            "<i class='material-icons'>add_a_photo</i>" => "add a photo",
            "<i class='material-icons'>add_alarm</i>" => "add alarm",
            "<i class='material-icons'>add_alert</i>" => "add alert",
            "<i class='material-icons'>add_box</i>" => "add box",
            "<i class='material-icons'>add_circle</i>" => "add circle",
            "<i class='material-icons'>add_circle_outline</i>" => "add circle outline",
            "<i class='material-icons'>add_location</i>" => "add location",
            "<i class='material-icons'>add_shopping_cart</i>" => "add shopping cart",
            "<i class='material-icons'>add_to_photos</i>" => "add to photos",
            "<i class='material-icons'>add_to_queue</i>" => "add to queue",
            "<i class='material-icons'>adjust</i>" => "adjust",
            "<i class='material-icons'>airline_seat_flat</i>" => "airline seat flat",
            "<i class='material-icons'>airline_seat_flat_angled</i>" => "airline seat flat angled",
            "<i class='material-icons'>airline_seat_individual_suite</i>" => "airline seat individual suite",
            "<i class='material-icons'>airline_seat_legroom_extra</i>" => "airline seat legroom extra",
            "<i class='material-icons'>airline_seat_legroom_normal</i>" => "airline seat legroom normal",
            "<i class='material-icons'>airline_seat_legroom_reduced</i>" => "airline seat legroom reduced",
            "<i class='material-icons'>airline_seat_recline_extra</i>" => "airline seat recline extra",
            "<i class='material-icons'>airline_seat_recline_normal</i>" => "airline seat recline normal",
            "<i class='material-icons'>airplanemode_active</i>" => "airplanemode active",
            "<i class='material-icons'>airplanemode_inactive</i>" => "airplanemode inactive",
            "<i class='material-icons'>airplay</i>" => "airplay",
            "<i class='material-icons'>airport_shuttle</i>" => "airport shuttle",
            "<i class='material-icons'>alarm</i>" => "alarm",
            "<i class='material-icons'>alarm_add</i>" => "alarm add",
            "<i class='material-icons'>alarm_off</i>" => "alarm off",
            "<i class='material-icons'>alarm_on</i>" => "alarm on",
            "<i class='material-icons'>album</i>" => "album",
            "<i class='material-icons'>all_inclusive</i>" => "all inclusive",
            "<i class='material-icons'>all_out</i>" => "all out",
            "<i class='material-icons'>android</i>" => "android",
            "<i class='material-icons'>announcement</i>" => "announcement",
            "<i class='material-icons'>apps</i>" => "apps",
            "<i class='material-icons'>archive</i>" => "archive",
            "<i class='material-icons'>arrow_back</i>" => "arrow back",
            "<i class='material-icons'>arrow_downward</i>" => "arrow downward",
            "<i class='material-icons'>arrow_drop_down</i>" => "arrow drop down",
            "<i class='material-icons'>arrow_drop_down_circle</i>" => "arrow drop down circle",
            "<i class='material-icons'>arrow_drop_up</i>" => "arrow drop up",
            "<i class='material-icons'>arrow_forward</i>" => "arrow forward",
            "<i class='material-icons'>arrow_upward</i>" => "arrow upward",
            "<i class='material-icons'>art_track</i>" => "art track",
            "<i class='material-icons'>aspect_ratio</i>" => "aspect ratio",
            "<i class='material-icons'>assessment</i>" => "assessment",
            "<i class='material-icons'>assignment</i>" => "assignment",
            "<i class='material-icons'>assignment_ind</i>" => "assignment ind",
            "<i class='material-icons'>assignment_late</i>" => "assignment late",
            "<i class='material-icons'>assignment_return</i>" => "assignment return",
            "<i class='material-icons'>assignment_returned</i>" => "assignment returned",
            "<i class='material-icons'>assignment_turned_in</i>" => "assignment turned in",
            "<i class='material-icons'>assistant</i>" => "assistant",
            "<i class='material-icons'>assistant_photo</i>" => "assistant photo",
            "<i class='material-icons'>attach_file</i>" => "attach file",
            "<i class='material-icons'>attach_money</i>" => "attach money",
            "<i class='material-icons'>attachment</i>" => "attachment",
            "<i class='material-icons'>audiotrack</i>" => "audiotrack",
            "<i class='material-icons'>autorenew</i>" => "autorenew",
            "<i class='material-icons'>av_timer</i>" => "av timer",
            "<i class='material-icons'>backspace</i>" => "backspace",
            "<i class='material-icons'>backup</i>" => "backup",
            "<i class='material-icons'>battery_alert</i>" => "battery alert",
            "<i class='material-icons'>battery_charging_full</i>" => "battery charging full",
            "<i class='material-icons'>battery_full</i>" => "battery full",
            "<i class='material-icons'>battery_std</i>" => "battery std",
            "<i class='material-icons'>battery_unknown</i>" => "battery unknown",
            "<i class='material-icons'>beach_access</i>" => "beach access",
            "<i class='material-icons'>beenhere</i>" => "beenhere",
            "<i class='material-icons'>block</i>" => "block",
            "<i class='material-icons'>bluetooth</i>" => "bluetooth",
            "<i class='material-icons'>bluetooth_audio</i>" => "bluetooth audio",
            "<i class='material-icons'>bluetooth_connected</i>" => "bluetooth connected",
            "<i class='material-icons'>bluetooth_disabled</i>" => "bluetooth disabled",
            "<i class='material-icons'>bluetooth_searching</i>" => "bluetooth searching",
            "<i class='material-icons'>blur_circular</i>" => "blur circular",
            "<i class='material-icons'>blur_linear</i>" => "blur linear",
            "<i class='material-icons'>blur_off</i>" => "blur off",
            "<i class='material-icons'>blur_on</i>" => "blur on",
            "<i class='material-icons'>book</i>" => "book",
            "<i class='material-icons'>bookmark</i>" => "bookmark",
            "<i class='material-icons'>bookmark_border</i>" => "bookmark border",
            "<i class='material-icons'>border_all</i>" => "border all",
            "<i class='material-icons'>border_bottom</i>" => "border bottom",
            "<i class='material-icons'>border_clear</i>" => "border clear",
            "<i class='material-icons'>border_color</i>" => "border color",
            "<i class='material-icons'>border_horizontal</i>" => "border horizontal",
            "<i class='material-icons'>border_inner</i>" => "border inner",
            "<i class='material-icons'>border_left</i>" => "border left",
            "<i class='material-icons'>border_outer</i>" => "border outer",
            "<i class='material-icons'>border_right</i>" => "border right",
            "<i class='material-icons'>border_style</i>" => "border style",
            "<i class='material-icons'>border_top</i>" => "border top",
            "<i class='material-icons'>border_vertical</i>" => "border vertical",
            "<i class='material-icons'>branding_watermark</i>" => "branding watermark",
            "<i class='material-icons'>brightness_1</i>" => "brightness 1",
            "<i class='material-icons'>brightness_2</i>" => "brightness 2",
            "<i class='material-icons'>brightness_3</i>" => "brightness 3",
            "<i class='material-icons'>brightness_4</i>" => "brightness 4",
            "<i class='material-icons'>brightness_5</i>" => "brightness 5",
            "<i class='material-icons'>brightness_6</i>" => "brightness 6",
            "<i class='material-icons'>brightness_7</i>" => "brightness 7",
            "<i class='material-icons'>brightness_auto</i>" => "brightness auto",
            "<i class='material-icons'>brightness_high</i>" => "brightness high",
            "<i class='material-icons'>brightness_low</i>" => "brightness low",
            "<i class='material-icons'>brightness_medium</i>" => "brightness medium",
            "<i class='material-icons'>broken_image</i>" => "broken image",
            "<i class='material-icons'>brush</i>" => "brush",
            "<i class='material-icons'>bubble_chart</i>" => "bubble chart",
            "<i class='material-icons'>bug_report</i>" => "bug report",
            "<i class='material-icons'>build</i>" => "build",
            "<i class='material-icons'>burst_mode</i>" => "burst mode",
            "<i class='material-icons'>business</i>" => "business",
            "<i class='material-icons'>business_center</i>" => "business center",
            "<i class='material-icons'>cached</i>" => "cached",
            "<i class='material-icons'>cake</i>" => "cake",
            "<i class='material-icons'>call</i>" => "call",
            "<i class='material-icons'>call_end</i>" => "call end",
            "<i class='material-icons'>call_made</i>" => "call made",
            "<i class='material-icons'>call_merge</i>" => "call merge",
            "<i class='material-icons'>call_missed</i>" => "call missed",
            "<i class='material-icons'>call_missed_outgoing</i>" => "call missed outgoing",
            "<i class='material-icons'>call_received</i>" => "call received",
            "<i class='material-icons'>call_split</i>" => "call split",
            "<i class='material-icons'>call_to_action</i>" => "call to action",
            "<i class='material-icons'>camera</i>" => "camera",
            "<i class='material-icons'>camera_alt</i>" => "camera alt",
            "<i class='material-icons'>camera_enhance</i>" => "camera enhance",
            "<i class='material-icons'>camera_front</i>" => "camera front",
            "<i class='material-icons'>camera_rear</i>" => "camera rear",
            "<i class='material-icons'>camera_roll</i>" => "camera roll",
            "<i class='material-icons'>cancel</i>" => "cancel",
            "<i class='material-icons'>card_giftcard</i>" => "card giftcard",
            "<i class='material-icons'>card_membership</i>" => "card membership",
            "<i class='material-icons'>card_travel</i>" => "card travel",
            "<i class='material-icons'>casino</i>" => "casino",
            "<i class='material-icons'>cast</i>" => "cast",
            "<i class='material-icons'>cast_connected</i>" => "cast connected",
            "<i class='material-icons'>center_focus_strong</i>" => "center focus strong",
            "<i class='material-icons'>center_focus_weak</i>" => "center focus weak",
            "<i class='material-icons'>change_history</i>" => "change history",
            "<i class='material-icons'>chat</i>" => "chat",
            "<i class='material-icons'>chat_bubble</i>" => "chat bubble",
            "<i class='material-icons'>chat_bubble_outline</i>" => "chat bubble outline",
            "<i class='material-icons'>check</i>" => "check",
            "<i class='material-icons'>check_box</i>" => "check box",
            "<i class='material-icons'>check_box_outline_blank</i>" => "check box outline blank",
            "<i class='material-icons'>check_circle</i>" => "check circle",
            "<i class='material-icons'>chevron_left</i>" => "chevron left",
            "<i class='material-icons'>chevron_right</i>" => "chevron right",
            "<i class='material-icons'>child_care</i>" => "child care",
            "<i class='material-icons'>child_friendly</i>" => "child friendly",
            "<i class='material-icons'>chrome_reader_mode</i>" => "chrome reader mode",
            "<i class='material-icons'>class</i>" => "class",
            "<i class='material-icons'>clear</i>" => "clear",
            "<i class='material-icons'>clear_all</i>" => "clear all",
            "<i class='material-icons'>close</i>" => "close",
            "<i class='material-icons'>closed_caption</i>" => "closed caption",
            "<i class='material-icons'>cloud</i>" => "cloud",
            "<i class='material-icons'>cloud_circle</i>" => "cloud circle",
            "<i class='material-icons'>cloud_done</i>" => "cloud done",
            "<i class='material-icons'>cloud_download</i>" => "cloud download",
            "<i class='material-icons'>cloud_off</i>" => "cloud off",
            "<i class='material-icons'>cloud_queue</i>" => "cloud queue",
            "<i class='material-icons'>cloud_upload</i>" => "cloud upload",
            "<i class='material-icons'>code</i>" => "code",
            "<i class='material-icons'>collections</i>" => "collections",
            "<i class='material-icons'>collections_bookmark</i>" => "collections bookmark",
            "<i class='material-icons'>color_lens</i>" => "color lens",
            "<i class='material-icons'>colorize</i>" => "colorize",
            "<i class='material-icons'>comment</i>" => "comment",
            "<i class='material-icons'>compare</i>" => "compare",
            "<i class='material-icons'>compare_arrows</i>" => "compare arrows",
            "<i class='material-icons'>computer</i>" => "computer",
            "<i class='material-icons'>confirmation_number</i>" => "confirmation number",
            "<i class='material-icons'>contact_mail</i>" => "contact mail",
            "<i class='material-icons'>contact_phone</i>" => "contact phone",
            "<i class='material-icons'>contacts</i>" => "contacts",
            "<i class='material-icons'>content_copy</i>" => "content copy",
            "<i class='material-icons'>content_cut</i>" => "content cut",
            "<i class='material-icons'>content_paste</i>" => "content paste",
            "<i class='material-icons'>control_point</i>" => "control point",
            "<i class='material-icons'>control_point_duplicate</i>" => "control point duplicate",
            "<i class='material-icons'>copyright</i>" => "copyright",
            "<i class='material-icons'>create</i>" => "create",
            "<i class='material-icons'>create_new_folder</i>" => "create new folder",
            "<i class='material-icons'>credit_card</i>" => "credit card",
            "<i class='material-icons'>crop</i>" => "crop",
            "<i class='material-icons'>crop_16_9</i>" => "crop 16 9",
            "<i class='material-icons'>crop_3_2</i>" => "crop 3 2",
            "<i class='material-icons'>crop_5_4</i>" => "crop 5 4",
            "<i class='material-icons'>crop_7_5</i>" => "crop 7 5",
            "<i class='material-icons'>crop_din</i>" => "crop din",
            "<i class='material-icons'>crop_free</i>" => "crop free",
            "<i class='material-icons'>crop_landscape</i>" => "crop landscape",
            "<i class='material-icons'>crop_original</i>" => "crop original",
            "<i class='material-icons'>crop_portrait</i>" => "crop portrait",
            "<i class='material-icons'>crop_rotate</i>" => "crop rotate",
            "<i class='material-icons'>crop_square</i>" => "crop square",
            "<i class='material-icons'>dashboard</i>" => "dashboard",
            "<i class='material-icons'>data_usage</i>" => "data usage",
            "<i class='material-icons'>date_range</i>" => "date range",
            "<i class='material-icons'>dehaze</i>" => "dehaze",
            "<i class='material-icons'>delete</i>" => "delete",
            "<i class='material-icons'>delete_forever</i>" => "delete forever",
            "<i class='material-icons'>delete_sweep</i>" => "delete sweep",
            "<i class='material-icons'>description</i>" => "description",
            "<i class='material-icons'>desktop_mac</i>" => "desktop mac",
            "<i class='material-icons'>desktop_windows</i>" => "desktop windows",
            "<i class='material-icons'>details</i>" => "details",
            "<i class='material-icons'>developer_board</i>" => "developer board",
            "<i class='material-icons'>developer_mode</i>" => "developer mode",
            "<i class='material-icons'>device_hub</i>" => "device hub",
            "<i class='material-icons'>devices</i>" => "devices",
            "<i class='material-icons'>devices_other</i>" => "devices other",
            "<i class='material-icons'>dialer_sip</i>" => "dialer sip",
            "<i class='material-icons'>dialpad</i>" => "dialpad",
            "<i class='material-icons'>directions</i>" => "directions",
            "<i class='material-icons'>directions_bike</i>" => "directions bike",
            "<i class='material-icons'>directions_boat</i>" => "directions boat",
            "<i class='material-icons'>directions_bus</i>" => "directions bus",
            "<i class='material-icons'>directions_car</i>" => "directions car",
            "<i class='material-icons'>directions_railway</i>" => "directions railway",
            "<i class='material-icons'>directions_run</i>" => "directions run",
            "<i class='material-icons'>directions_subway</i>" => "directions subway",
            "<i class='material-icons'>directions_transit</i>" => "directions transit",
            "<i class='material-icons'>directions_walk</i>" => "directions walk",
            "<i class='material-icons'>disc_full</i>" => "disc full",
            "<i class='material-icons'>dns</i>" => "dns",
            "<i class='material-icons'>do_not_disturb</i>" => "do not disturb",
            "<i class='material-icons'>do_not_disturb_alt</i>" => "do not disturb alt",
            "<i class='material-icons'>do_not_disturb_off</i>" => "do not disturb off",
            "<i class='material-icons'>do_not_disturb_on</i>" => "do not disturb on",
            "<i class='material-icons'>dock</i>" => "dock",
            "<i class='material-icons'>domain</i>" => "domain",
            "<i class='material-icons'>done</i>" => "done",
            "<i class='material-icons'>done_all</i>" => "done all",
            "<i class='material-icons'>donut_large</i>" => "donut large",
            "<i class='material-icons'>donut_small</i>" => "donut small",
            "<i class='material-icons'>drafts</i>" => "drafts",
            "<i class='material-icons'>drag_handle</i>" => "drag handle",
            "<i class='material-icons'>drive_eta</i>" => "drive eta",
            "<i class='material-icons'>dvr</i>" => "dvr",
            "<i class='material-icons'>edit</i>" => "edit",
            "<i class='material-icons'>edit_location</i>" => "edit location",
            "<i class='material-icons'>eject</i>" => "eject",
            "<i class='material-icons'>email</i>" => "email",
            "<i class='material-icons'>enhanced_encryption</i>" => "enhanced encryption",
            "<i class='material-icons'>equalizer</i>" => "equalizer",
            "<i class='material-icons'>error</i>" => "error",
            "<i class='material-icons'>error_outline</i>" => "error outline",
            "<i class='material-icons'>euro_symbol</i>" => "euro symbol",
            "<i class='material-icons'>ev_station</i>" => "ev station",
            "<i class='material-icons'>event</i>" => "event",
            "<i class='material-icons'>event_available</i>" => "event available",
            "<i class='material-icons'>event_busy</i>" => "event busy",
            "<i class='material-icons'>event_note</i>" => "event note",
            "<i class='material-icons'>event_seat</i>" => "event seat",
            "<i class='material-icons'>exit_to_app</i>" => "exit to app",
            "<i class='material-icons'>expand_less</i>" => "expand less",
            "<i class='material-icons'>expand_more</i>" => "expand more",
            "<i class='material-icons'>explicit</i>" => "explicit",
            "<i class='material-icons'>explore</i>" => "explore",
            "<i class='material-icons'>exposure</i>" => "exposure",
            "<i class='material-icons'>exposure_neg_1</i>" => "exposure neg 1",
            "<i class='material-icons'>exposure_neg_2</i>" => "exposure neg 2",
            "<i class='material-icons'>exposure_plus_1</i>" => "exposure plus 1",
            "<i class='material-icons'>exposure_plus_2</i>" => "exposure plus 2",
            "<i class='material-icons'>exposure_zero</i>" => "exposure zero",
            "<i class='material-icons'>extension</i>" => "extension",
            "<i class='material-icons'>face</i>" => "face",
            "<i class='material-icons'>fast_forward</i>" => "fast forward",
            "<i class='material-icons'>fast_rewind</i>" => "fast rewind",
            "<i class='material-icons'>favorite</i>" => "favorite",
            "<i class='material-icons'>favorite_border</i>" => "favorite border",
            "<i class='material-icons'>featured_play_list</i>" => "featured play list",
            "<i class='material-icons'>featured_video</i>" => "featured video",
            "<i class='material-icons'>feedback</i>" => "feedback",
            "<i class='material-icons'>fiber_dvr</i>" => "fiber dvr",
            "<i class='material-icons'>fiber_manual_record</i>" => "fiber manual record",
            "<i class='material-icons'>fiber_new</i>" => "fiber new",
            "<i class='material-icons'>fiber_pin</i>" => "fiber pin",
            "<i class='material-icons'>fiber_smart_record</i>" => "fiber smart record",
            "<i class='material-icons'>file_download</i>" => "file download",
            "<i class='material-icons'>file_upload</i>" => "file upload",
            "<i class='material-icons'>filter</i>" => "filter",
            "<i class='material-icons'>filter_1</i>" => "filter 1",
            "<i class='material-icons'>filter_2</i>" => "filter 2",
            "<i class='material-icons'>filter_3</i>" => "filter 3",
            "<i class='material-icons'>filter_4</i>" => "filter 4",
            "<i class='material-icons'>filter_5</i>" => "filter 5",
            "<i class='material-icons'>filter_6</i>" => "filter 6",
            "<i class='material-icons'>filter_7</i>" => "filter 7",
            "<i class='material-icons'>filter_8</i>" => "filter 8",
            "<i class='material-icons'>filter_9</i>" => "filter 9",
            "<i class='material-icons'>filter_9_plus</i>" => "filter 9 plus",
            "<i class='material-icons'>filter_b_and_w</i>" => "filter b and w",
            "<i class='material-icons'>filter_center_focus</i>" => "filter center focus",
            "<i class='material-icons'>filter_drama</i>" => "filter drama",
            "<i class='material-icons'>filter_frames</i>" => "filter frames",
            "<i class='material-icons'>filter_hdr</i>" => "filter hdr",
            "<i class='material-icons'>filter_list</i>" => "filter list",
            "<i class='material-icons'>filter_none</i>" => "filter none",
            "<i class='material-icons'>filter_tilt_shift</i>" => "filter tilt shift",
            "<i class='material-icons'>filter_vintage</i>" => "filter vintage",
            "<i class='material-icons'>find_in_page</i>" => "find in page",
            "<i class='material-icons'>find_replace</i>" => "find replace",
            "<i class='material-icons'>fingerprint</i>" => "fingerprint",
            "<i class='material-icons'>first_page</i>" => "first page",
            "<i class='material-icons'>fitness_center</i>" => "fitness center",
            "<i class='material-icons'>flag</i>" => "flag",
            "<i class='material-icons'>flare</i>" => "flare",
            "<i class='material-icons'>flash_auto</i>" => "flash auto",
            "<i class='material-icons'>flash_off</i>" => "flash off",
            "<i class='material-icons'>flash_on</i>" => "flash on",
            "<i class='material-icons'>flight</i>" => "flight",
            "<i class='material-icons'>flight_land</i>" => "flight land",
            "<i class='material-icons'>flight_takeoff</i>" => "flight takeoff",
            "<i class='material-icons'>flip</i>" => "flip",
            "<i class='material-icons'>flip_to_back</i>" => "flip to back",
            "<i class='material-icons'>flip_to_front</i>" => "flip to front",
            "<i class='material-icons'>folder</i>" => "folder",
            "<i class='material-icons'>folder_open</i>" => "folder open",
            "<i class='material-icons'>folder_shared</i>" => "folder shared",
            "<i class='material-icons'>folder_special</i>" => "folder special",
            "<i class='material-icons'>font_download</i>" => "font download",
            "<i class='material-icons'>format_align_center</i>" => "format align center",
            "<i class='material-icons'>format_align_justify</i>" => "format align justify",
            "<i class='material-icons'>format_align_left</i>" => "format align left",
            "<i class='material-icons'>format_align_right</i>" => "format align right",
            "<i class='material-icons'>format_bold</i>" => "format bold",
            "<i class='material-icons'>format_clear</i>" => "format clear",
            "<i class='material-icons'>format_color_fill</i>" => "format color fill",
            "<i class='material-icons'>format_color_reset</i>" => "format color reset",
            "<i class='material-icons'>format_color_text</i>" => "format color text",
            "<i class='material-icons'>format_indent_decrease</i>" => "format indent decrease",
            "<i class='material-icons'>format_indent_increase</i>" => "format indent increase",
            "<i class='material-icons'>format_italic</i>" => "format italic",
            "<i class='material-icons'>format_line_spacing</i>" => "format line spacing",
            "<i class='material-icons'>format_list_bulleted</i>" => "format list bulleted",
            "<i class='material-icons'>format_list_numbered</i>" => "format list numbered",
            "<i class='material-icons'>format_paint</i>" => "format paint",
            "<i class='material-icons'>format_quote</i>" => "format quote",
            "<i class='material-icons'>format_shapes</i>" => "format shapes",
            "<i class='material-icons'>format_size</i>" => "format size",
            "<i class='material-icons'>format_strikethrough</i>" => "format strikethrough",
            "<i class='material-icons'>format_textdirection_l_to_r</i>" => "format textdirection l to r",
            "<i class='material-icons'>format_textdirection_r_to_l</i>" => "format textdirection r to l",
            "<i class='material-icons'>format_underlined</i>" => "format underlined",
            "<i class='material-icons'>forum</i>" => "forum",
            "<i class='material-icons'>forward</i>" => "forward",
            "<i class='material-icons'>forward_10</i>" => "forward 10",
            "<i class='material-icons'>forward_30</i>" => "forward 30",
            "<i class='material-icons'>forward_5</i>" => "forward 5",
            "<i class='material-icons'>free_breakfast</i>" => "free breakfast",
            "<i class='material-icons'>fullscreen</i>" => "fullscreen",
            "<i class='material-icons'>fullscreen_exit</i>" => "fullscreen exit",
            "<i class='material-icons'>functions</i>" => "functions",
            "<i class='material-icons'>g_translate</i>" => "g translate",
            "<i class='material-icons'>gamepad</i>" => "gamepad",
            "<i class='material-icons'>games</i>" => "games",
            "<i class='material-icons'>gavel</i>" => "gavel",
            "<i class='material-icons'>gesture</i>" => "gesture",
            "<i class='material-icons'>get_app</i>" => "get app",
            "<i class='material-icons'>gif</i>" => "gif",
            "<i class='material-icons'>golf_course</i>" => "golf course",
            "<i class='material-icons'>gps_fixed</i>" => "gps fixed",
            "<i class='material-icons'>gps_not_fixed</i>" => "gps not fixed",
            "<i class='material-icons'>gps_off</i>" => "gps off",
            "<i class='material-icons'>grade</i>" => "grade",
            "<i class='material-icons'>gradient</i>" => "gradient",
            "<i class='material-icons'>grain</i>" => "grain",
            "<i class='material-icons'>graphic_eq</i>" => "graphic eq",
            "<i class='material-icons'>grid_off</i>" => "grid off",
            "<i class='material-icons'>grid_on</i>" => "grid on",
            "<i class='material-icons'>group</i>" => "group",
            "<i class='material-icons'>group_add</i>" => "group add",
            "<i class='material-icons'>group_work</i>" => "group work",
            "<i class='material-icons'>hd</i>" => "hd",
            "<i class='material-icons'>hdr_off</i>" => "hdr off",
            "<i class='material-icons'>hdr_on</i>" => "hdr on",
            "<i class='material-icons'>hdr_strong</i>" => "hdr strong",
            "<i class='material-icons'>hdr_weak</i>" => "hdr weak",
            "<i class='material-icons'>headset</i>" => "headset",
            "<i class='material-icons'>headset_mic</i>" => "headset mic",
            "<i class='material-icons'>healing</i>" => "healing",
            "<i class='material-icons'>hearing</i>" => "hearing",
            "<i class='material-icons'>help</i>" => "help",
            "<i class='material-icons'>help_outline</i>" => "help outline",
            "<i class='material-icons'>high_quality</i>" => "high quality",
            "<i class='material-icons'>highlight</i>" => "highlight",
            "<i class='material-icons'>highlight_off</i>" => "highlight off",
            "<i class='material-icons'>history</i>" => "history",
            "<i class='material-icons'>home</i>" => "home",
            "<i class='material-icons'>hot_tub</i>" => "hot tub",
            "<i class='material-icons'>hotel</i>" => "hotel",
            "<i class='material-icons'>hourglass_empty</i>" => "hourglass empty",
            "<i class='material-icons'>hourglass_full</i>" => "hourglass full",
            "<i class='material-icons'>http</i>" => "http",
            "<i class='material-icons'>https</i>" => "https",
            "<i class='material-icons'>image</i>" => "image",
            "<i class='material-icons'>image_aspect_ratio</i>" => "image aspect ratio",
            "<i class='material-icons'>import_contacts</i>" => "import contacts",
            "<i class='material-icons'>import_export</i>" => "import export",
            "<i class='material-icons'>important_devices</i>" => "important devices",
            "<i class='material-icons'>inbox</i>" => "inbox",
            "<i class='material-icons'>indeterminate_check_box</i>" => "indeterminate check box",
            "<i class='material-icons'>info</i>" => "info",
            "<i class='material-icons'>info_outline</i>" => "info outline",
            "<i class='material-icons'>input</i>" => "input",
            "<i class='material-icons'>insert_chart</i>" => "insert chart",
            "<i class='material-icons'>insert_comment</i>" => "insert comment",
            "<i class='material-icons'>insert_drive_file</i>" => "insert drive file",
            "<i class='material-icons'>insert_emoticon</i>" => "insert emoticon",
            "<i class='material-icons'>insert_invitation</i>" => "insert invitation",
            "<i class='material-icons'>insert_link</i>" => "insert link",
            "<i class='material-icons'>insert_photo</i>" => "insert photo",
            "<i class='material-icons'>invert_colors</i>" => "invert colors",
            "<i class='material-icons'>invert_colors_off</i>" => "invert colors off",
            "<i class='material-icons'>iso</i>" => "iso",
            "<i class='material-icons'>keyboard</i>" => "keyboard",
            "<i class='material-icons'>keyboard_arrow_down</i>" => "keyboard arrow down",
            "<i class='material-icons'>keyboard_arrow_left</i>" => "keyboard arrow left",
            "<i class='material-icons'>keyboard_arrow_right</i>" => "keyboard arrow right",
            "<i class='material-icons'>keyboard_arrow_up</i>" => "keyboard arrow up",
            "<i class='material-icons'>keyboard_backspace</i>" => "keyboard backspace",
            "<i class='material-icons'>keyboard_capslock</i>" => "keyboard capslock",
            "<i class='material-icons'>keyboard_hide</i>" => "keyboard hide",
            "<i class='material-icons'>keyboard_return</i>" => "keyboard return",
            "<i class='material-icons'>keyboard_tab</i>" => "keyboard tab",
            "<i class='material-icons'>keyboard_voice</i>" => "keyboard voice",
            "<i class='material-icons'>kitchen</i>" => "kitchen",
            "<i class='material-icons'>label</i>" => "label",
            "<i class='material-icons'>label_outline</i>" => "label outline",
            "<i class='material-icons'>landscape</i>" => "landscape",
            "<i class='material-icons'>language</i>" => "language",
            "<i class='material-icons'>laptop</i>" => "laptop",
            "<i class='material-icons'>laptop_chromebook</i>" => "laptop chromebook",
            "<i class='material-icons'>laptop_mac</i>" => "laptop mac",
            "<i class='material-icons'>laptop_windows</i>" => "laptop windows",
            "<i class='material-icons'>last_page</i>" => "last page",
            "<i class='material-icons'>launch</i>" => "launch",
            "<i class='material-icons'>layers</i>" => "layers",
            "<i class='material-icons'>layers_clear</i>" => "layers clear",
            "<i class='material-icons'>leak_add</i>" => "leak add",
            "<i class='material-icons'>leak_remove</i>" => "leak remove",
            "<i class='material-icons'>lens</i>" => "lens",
            "<i class='material-icons'>library_add</i>" => "library add",
            "<i class='material-icons'>library_books</i>" => "library books",
            "<i class='material-icons'>library_music</i>" => "library music",
            "<i class='material-icons'>lightbulb_outline</i>" => "lightbulb outline",
            "<i class='material-icons'>line_style</i>" => "line style",
            "<i class='material-icons'>line_weight</i>" => "line weight",
            "<i class='material-icons'>linear_scale</i>" => "linear scale",
            "<i class='material-icons'>link</i>" => "link",
            "<i class='material-icons'>linked_camera</i>" => "linked camera",
            "<i class='material-icons'>list</i>" => "list",
            "<i class='material-icons'>live_help</i>" => "live help",
            "<i class='material-icons'>live_tv</i>" => "live tv",
            "<i class='material-icons'>local_activity</i>" => "local activity",
            "<i class='material-icons'>local_airport</i>" => "local airport",
            "<i class='material-icons'>local_atm</i>" => "local atm",
            "<i class='material-icons'>local_bar</i>" => "local bar",
            "<i class='material-icons'>local_cafe</i>" => "local cafe",
            "<i class='material-icons'>local_car_wash</i>" => "local car wash",
            "<i class='material-icons'>local_convenience_store</i>" => "local convenience store",
            "<i class='material-icons'>local_dining</i>" => "local dining",
            "<i class='material-icons'>local_drink</i>" => "local drink",
            "<i class='material-icons'>local_florist</i>" => "local florist",
            "<i class='material-icons'>local_gas_station</i>" => "local gas station",
            "<i class='material-icons'>local_grocery_store</i>" => "local grocery store",
            "<i class='material-icons'>local_hospital</i>" => "local hospital",
            "<i class='material-icons'>local_hotel</i>" => "local hotel",
            "<i class='material-icons'>local_laundry_service</i>" => "local laundry service",
            "<i class='material-icons'>local_library</i>" => "local library",
            "<i class='material-icons'>local_mall</i>" => "local mall",
            "<i class='material-icons'>local_movies</i>" => "local movies",
            "<i class='material-icons'>local_offer</i>" => "local offer",
            "<i class='material-icons'>local_parking</i>" => "local parking",
            "<i class='material-icons'>local_pharmacy</i>" => "local pharmacy",
            "<i class='material-icons'>local_phone</i>" => "local phone",
            "<i class='material-icons'>local_pizza</i>" => "local pizza",
            "<i class='material-icons'>local_play</i>" => "local play",
            "<i class='material-icons'>local_post_office</i>" => "local post office",
            "<i class='material-icons'>local_printshop</i>" => "local printshop",
            "<i class='material-icons'>local_see</i>" => "local see",
            "<i class='material-icons'>local_shipping</i>" => "local shipping",
            "<i class='material-icons'>local_taxi</i>" => "local taxi",
            "<i class='material-icons'>location_city</i>" => "location city",
            "<i class='material-icons'>location_disabled</i>" => "location disabled",
            "<i class='material-icons'>location_off</i>" => "location off",
            "<i class='material-icons'>location_on</i>" => "location on",
            "<i class='material-icons'>location_searching</i>" => "location searching",
            "<i class='material-icons'>lock</i>" => "lock",
            "<i class='material-icons'>lock_open</i>" => "lock open",
            "<i class='material-icons'>lock_outline</i>" => "lock outline",
            "<i class='material-icons'>looks</i>" => "looks",
            "<i class='material-icons'>looks_3</i>" => "looks 3",
            "<i class='material-icons'>looks_4</i>" => "looks 4",
            "<i class='material-icons'>looks_5</i>" => "looks 5",
            "<i class='material-icons'>looks_6</i>" => "looks 6",
            "<i class='material-icons'>looks_one</i>" => "looks one",
            "<i class='material-icons'>looks_two</i>" => "looks two",
            "<i class='material-icons'>loop</i>" => "loop",
            "<i class='material-icons'>loupe</i>" => "loupe",
            "<i class='material-icons'>low_priority</i>" => "low priority",
            "<i class='material-icons'>loyalty</i>" => "loyalty",
            "<i class='material-icons'>mail</i>" => "mail",
            "<i class='material-icons'>mail_outline</i>" => "mail outline",
            "<i class='material-icons'>map</i>" => "map",
            "<i class='material-icons'>markunread</i>" => "markunread",
            "<i class='material-icons'>markunread_mailbox</i>" => "markunread mailbox",
            "<i class='material-icons'>memory</i>" => "memory",
            "<i class='material-icons'>menu</i>" => "menu",
            "<i class='material-icons'>merge_type</i>" => "merge type",
            "<i class='material-icons'>message</i>" => "message",
            "<i class='material-icons'>mic</i>" => "mic",
            "<i class='material-icons'>mic_none</i>" => "mic none",
            "<i class='material-icons'>mic_off</i>" => "mic off",
            "<i class='material-icons'>mms</i>" => "mms",
            "<i class='material-icons'>mode_comment</i>" => "mode comment",
            "<i class='material-icons'>mode_edit</i>" => "mode edit",
            "<i class='material-icons'>monetization_on</i>" => "monetization on",
            "<i class='material-icons'>money_off</i>" => "money off",
            "<i class='material-icons'>monochrome_photos</i>" => "monochrome photos",
            "<i class='material-icons'>mood</i>" => "mood",
            "<i class='material-icons'>mood_bad</i>" => "mood bad",
            "<i class='material-icons'>more</i>" => "more",
            "<i class='material-icons'>more_horiz</i>" => "more horiz",
            "<i class='material-icons'>more_vert</i>" => "more vert",
            "<i class='material-icons'>motorcycle</i>" => "motorcycle",
            "<i class='material-icons'>mouse</i>" => "mouse",
            "<i class='material-icons'>move_to_inbox</i>" => "move to inbox",
            "<i class='material-icons'>movie</i>" => "movie",
            "<i class='material-icons'>movie_creation</i>" => "movie creation",
            "<i class='material-icons'>movie_filter</i>" => "movie filter",
            "<i class='material-icons'>multiline_chart</i>" => "multiline chart",
            "<i class='material-icons'>music_note</i>" => "music note",
            "<i class='material-icons'>music_video</i>" => "music video",
            "<i class='material-icons'>my_location</i>" => "my location",
            "<i class='material-icons'>nature</i>" => "nature",
            "<i class='material-icons'>nature_people</i>" => "nature people",
            "<i class='material-icons'>navigate_before</i>" => "navigate before",
            "<i class='material-icons'>navigate_next</i>" => "navigate next",
            "<i class='material-icons'>navigation</i>" => "navigation",
            "<i class='material-icons'>near_me</i>" => "near me",
            "<i class='material-icons'>network_cell</i>" => "network cell",
            "<i class='material-icons'>network_check</i>" => "network check",
            "<i class='material-icons'>network_locked</i>" => "network locked",
            "<i class='material-icons'>network_wifi</i>" => "network wifi",
            "<i class='material-icons'>new_releases</i>" => "new releases",
            "<i class='material-icons'>next_week</i>" => "next week",
            "<i class='material-icons'>nfc</i>" => "nfc",
            "<i class='material-icons'>no_encryption</i>" => "no encryption",
            "<i class='material-icons'>no_sim</i>" => "no sim",
            "<i class='material-icons'>not_interested</i>" => "not interested",
            "<i class='material-icons'>note</i>" => "note",
            "<i class='material-icons'>note_add</i>" => "note add",
            "<i class='material-icons'>notifications</i>" => "notifications",
            "<i class='material-icons'>notifications_active</i>" => "notifications active",
            "<i class='material-icons'>notifications_none</i>" => "notifications none",
            "<i class='material-icons'>notifications_off</i>" => "notifications off",
            "<i class='material-icons'>notifications_paused</i>" => "notifications paused",
            "<i class='material-icons'>offline_pin</i>" => "offline pin",
            "<i class='material-icons'>ondemand_video</i>" => "ondemand video",
            "<i class='material-icons'>opacity</i>" => "opacity",
            "<i class='material-icons'>open_in_browser</i>" => "open in browser",
            "<i class='material-icons'>open_in_new</i>" => "open in new",
            "<i class='material-icons'>open_with</i>" => "open with",
            "<i class='material-icons'>pages</i>" => "pages",
            "<i class='material-icons'>pageview</i>" => "pageview",
            "<i class='material-icons'>palette</i>" => "palette",
            "<i class='material-icons'>pan_tool</i>" => "pan tool",
            "<i class='material-icons'>panorama</i>" => "panorama",
            "<i class='material-icons'>panorama_fish_eye</i>" => "panorama fish eye",
            "<i class='material-icons'>panorama_horizontal</i>" => "panorama horizontal",
            "<i class='material-icons'>panorama_vertical</i>" => "panorama vertical",
            "<i class='material-icons'>panorama_wide_angle</i>" => "panorama wide angle",
            "<i class='material-icons'>party_mode</i>" => "party mode",
            "<i class='material-icons'>pause</i>" => "pause",
            "<i class='material-icons'>pause_circle_filled</i>" => "pause circle filled",
            "<i class='material-icons'>pause_circle_outline</i>" => "pause circle outline",
            "<i class='material-icons'>payment</i>" => "payment",
            "<i class='material-icons'>people</i>" => "people",
            "<i class='material-icons'>people_outline</i>" => "people outline",
            "<i class='material-icons'>perm_camera_mic</i>" => "perm camera mic",
            "<i class='material-icons'>perm_contact_calendar</i>" => "perm contact calendar",
            "<i class='material-icons'>perm_data_setting</i>" => "perm data setting",
            "<i class='material-icons'>perm_device_information</i>" => "perm device information",
            "<i class='material-icons'>perm_identity</i>" => "perm identity",
            "<i class='material-icons'>perm_media</i>" => "perm media",
            "<i class='material-icons'>perm_phone_msg</i>" => "perm phone msg",
            "<i class='material-icons'>perm_scan_wifi</i>" => "perm scan wifi",
            "<i class='material-icons'>person</i>" => "person",
            "<i class='material-icons'>person_add</i>" => "person add",
            "<i class='material-icons'>person_outline</i>" => "person outline",
            "<i class='material-icons'>person_pin</i>" => "person pin",
            "<i class='material-icons'>person_pin_circle</i>" => "person pin circle",
            "<i class='material-icons'>personal_video</i>" => "personal video",
            "<i class='material-icons'>pets</i>" => "pets",
            "<i class='material-icons'>phone</i>" => "phone",
            "<i class='material-icons'>phone_android</i>" => "phone android",
            "<i class='material-icons'>phone_bluetooth_speaker</i>" => "phone bluetooth speaker",
            "<i class='material-icons'>phone_forwarded</i>" => "phone forwarded",
            "<i class='material-icons'>phone_in_talk</i>" => "phone in talk",
            "<i class='material-icons'>phone_iphone</i>" => "phone iphone",
            "<i class='material-icons'>phone_locked</i>" => "phone locked",
            "<i class='material-icons'>phone_missed</i>" => "phone missed",
            "<i class='material-icons'>phone_paused</i>" => "phone paused",
            "<i class='material-icons'>phonelink</i>" => "phonelink",
            "<i class='material-icons'>phonelink_erase</i>" => "phonelink erase",
            "<i class='material-icons'>phonelink_lock</i>" => "phonelink lock",
            "<i class='material-icons'>phonelink_off</i>" => "phonelink off",
            "<i class='material-icons'>phonelink_ring</i>" => "phonelink ring",
            "<i class='material-icons'>phonelink_setup</i>" => "phonelink setup",
            "<i class='material-icons'>photo</i>" => "photo",
            "<i class='material-icons'>photo_album</i>" => "photo album",
            "<i class='material-icons'>photo_camera</i>" => "photo camera",
            "<i class='material-icons'>photo_filter</i>" => "photo filter",
            "<i class='material-icons'>photo_library</i>" => "photo library",
            "<i class='material-icons'>photo_size_select_actual</i>" => "photo size select actual",
            "<i class='material-icons'>photo_size_select_large</i>" => "photo size select large",
            "<i class='material-icons'>photo_size_select_small</i>" => "photo size select small",
            "<i class='material-icons'>picture_as_pdf</i>" => "picture as pdf",
            "<i class='material-icons'>picture_in_picture</i>" => "picture in picture",
            "<i class='material-icons'>picture_in_picture_alt</i>" => "picture in picture alt",
            "<i class='material-icons'>pie_chart</i>" => "pie chart",
            "<i class='material-icons'>pie_chart_outlined</i>" => "pie chart outlined",
            "<i class='material-icons'>pin_drop</i>" => "pin drop",
            "<i class='material-icons'>place</i>" => "place",
            "<i class='material-icons'>play_arrow</i>" => "play arrow",
            "<i class='material-icons'>play_circle_filled</i>" => "play circle filled",
            "<i class='material-icons'>play_circle_outline</i>" => "play circle outline",
            "<i class='material-icons'>play_for_work</i>" => "play for work",
            "<i class='material-icons'>playlist_add</i>" => "playlist add",
            "<i class='material-icons'>playlist_add_check</i>" => "playlist add check",
            "<i class='material-icons'>playlist_play</i>" => "playlist play",
            "<i class='material-icons'>plus_one</i>" => "plus one",
            "<i class='material-icons'>poll</i>" => "poll",
            "<i class='material-icons'>polymer</i>" => "polymer",
            "<i class='material-icons'>pool</i>" => "pool",
            "<i class='material-icons'>portable_wifi_off</i>" => "portable wifi off",
            "<i class='material-icons'>portrait</i>" => "portrait",
            "<i class='material-icons'>power</i>" => "power",
            "<i class='material-icons'>power_input</i>" => "power input",
            "<i class='material-icons'>power_settings_new</i>" => "power settings new",
            "<i class='material-icons'>pregnant_woman</i>" => "pregnant woman",
            "<i class='material-icons'>present_to_all</i>" => "present to all",
            "<i class='material-icons'>print</i>" => "print",
            "<i class='material-icons'>priority_high</i>" => "priority high",
            "<i class='material-icons'>public</i>" => "public",
            "<i class='material-icons'>publish</i>" => "publish",
            "<i class='material-icons'>query_builder</i>" => "query builder",
            "<i class='material-icons'>question_answer</i>" => "question answer",
            "<i class='material-icons'>queue</i>" => "queue",
            "<i class='material-icons'>queue_music</i>" => "queue music",
            "<i class='material-icons'>queue_play_next</i>" => "queue play next",
            "<i class='material-icons'>radio</i>" => "radio",
            "<i class='material-icons'>radio_button_checked</i>" => "radio button checked",
            "<i class='material-icons'>radio_button_unchecked</i>" => "radio button unchecked",
            "<i class='material-icons'>rate_review</i>" => "rate review",
            "<i class='material-icons'>receipt</i>" => "receipt",
            "<i class='material-icons'>recent_actors</i>" => "recent actors",
            "<i class='material-icons'>record_voice_over</i>" => "record voice over",
            "<i class='material-icons'>redeem</i>" => "redeem",
            "<i class='material-icons'>redo</i>" => "redo",
            "<i class='material-icons'>refresh</i>" => "refresh",
            "<i class='material-icons'>remove</i>" => "remove",
            "<i class='material-icons'>remove_circle</i>" => "remove circle",
            "<i class='material-icons'>remove_circle_outline</i>" => "remove circle outline",
            "<i class='material-icons'>remove_from_queue</i>" => "remove from queue",
            "<i class='material-icons'>remove_red_eye</i>" => "remove red eye",
            "<i class='material-icons'>remove_shopping_cart</i>" => "remove shopping cart",
            "<i class='material-icons'>reorder</i>" => "reorder",
            "<i class='material-icons'>repeat</i>" => "repeat",
            "<i class='material-icons'>repeat_one</i>" => "repeat one",
            "<i class='material-icons'>replay</i>" => "replay",
            "<i class='material-icons'>replay_10</i>" => "replay 10",
            "<i class='material-icons'>replay_30</i>" => "replay 30",
            "<i class='material-icons'>replay_5</i>" => "replay 5",
            "<i class='material-icons'>reply</i>" => "reply",
            "<i class='material-icons'>reply_all</i>" => "reply all",
            "<i class='material-icons'>report</i>" => "report",
            "<i class='material-icons'>report_problem</i>" => "report problem",
            "<i class='material-icons'>restaurant</i>" => "restaurant",
            "<i class='material-icons'>restaurant_menu</i>" => "restaurant menu",
            "<i class='material-icons'>restore</i>" => "restore",
            "<i class='material-icons'>restore_page</i>" => "restore page",
            "<i class='material-icons'>ring_volume</i>" => "ring volume",
            "<i class='material-icons'>room</i>" => "room",
            "<i class='material-icons'>room_service</i>" => "room service",
            "<i class='material-icons'>rotate_90_degrees_ccw</i>" => "rotate 90 degrees ccw",
            "<i class='material-icons'>rotate_left</i>" => "rotate left",
            "<i class='material-icons'>rotate_right</i>" => "rotate right",
            "<i class='material-icons'>rounded_corner</i>" => "rounded corner",
            "<i class='material-icons'>router</i>" => "router",
            "<i class='material-icons'>rowing</i>" => "rowing",
            "<i class='material-icons'>rss_feed</i>" => "rss feed",
            "<i class='material-icons'>rv_hookup</i>" => "rv hookup",
            "<i class='material-icons'>satellite</i>" => "satellite",
            "<i class='material-icons'>save</i>" => "save",
            "<i class='material-icons'>scanner</i>" => "scanner",
            "<i class='material-icons'>schedule</i>" => "schedule",
            "<i class='material-icons'>school</i>" => "school",
            "<i class='material-icons'>screen_lock_landscape</i>" => "screen lock landscape",
            "<i class='material-icons'>screen_lock_portrait</i>" => "screen lock portrait",
            "<i class='material-icons'>screen_lock_rotation</i>" => "screen lock rotation",
            "<i class='material-icons'>screen_rotation</i>" => "screen rotation",
            "<i class='material-icons'>screen_share</i>" => "screen share",
            "<i class='material-icons'>sd_card</i>" => "sd card",
            "<i class='material-icons'>sd_storage</i>" => "sd storage",
            "<i class='material-icons'>search</i>" => "search",
            "<i class='material-icons'>security</i>" => "security",
            "<i class='material-icons'>select_all</i>" => "select all",
            "<i class='material-icons'>send</i>" => "send",
            "<i class='material-icons'>sentiment_dissatisfied</i>" => "sentiment dissatisfied",
            "<i class='material-icons'>sentiment_neutral</i>" => "sentiment neutral",
            "<i class='material-icons'>sentiment_satisfied</i>" => "sentiment satisfied",
            "<i class='material-icons'>sentiment_very_dissatisfied</i>" => "sentiment very dissatisfied",
            "<i class='material-icons'>sentiment_very_satisfied</i>" => "sentiment very satisfied",
            "<i class='material-icons'>settings</i>" => "settings",
            "<i class='material-icons'>settings_applications</i>" => "settings applications",
            "<i class='material-icons'>settings_backup_restore</i>" => "settings backup restore",
            "<i class='material-icons'>settings_bluetooth</i>" => "settings bluetooth",
            "<i class='material-icons'>settings_brightness</i>" => "settings brightness",
            "<i class='material-icons'>settings_cell</i>" => "settings cell",
            "<i class='material-icons'>settings_ethernet</i>" => "settings ethernet",
            "<i class='material-icons'>settings_input_antenna</i>" => "settings input antenna",
            "<i class='material-icons'>settings_input_component</i>" => "settings input component",
            "<i class='material-icons'>settings_input_composite</i>" => "settings input composite",
            "<i class='material-icons'>settings_input_hdmi</i>" => "settings input hdmi",
            "<i class='material-icons'>settings_input_svideo</i>" => "settings input svideo",
            "<i class='material-icons'>settings_overscan</i>" => "settings overscan",
            "<i class='material-icons'>settings_phone</i>" => "settings phone",
            "<i class='material-icons'>settings_power</i>" => "settings power",
            "<i class='material-icons'>settings_remote</i>" => "settings remote",
            "<i class='material-icons'>settings_system_daydream</i>" => "settings system daydream",
            "<i class='material-icons'>settings_voice</i>" => "settings voice",
            "<i class='material-icons'>share</i>" => "share",
            "<i class='material-icons'>shop</i>" => "shop",
            "<i class='material-icons'>shop_two</i>" => "shop two",
            "<i class='material-icons'>shopping_basket</i>" => "shopping basket",
            "<i class='material-icons'>shopping_cart</i>" => "shopping cart",
            "<i class='material-icons'>short_text</i>" => "short text",
            "<i class='material-icons'>show_chart</i>" => "show chart",
            "<i class='material-icons'>shuffle</i>" => "shuffle",
            "<i class='material-icons'>signal_cellular_4_bar</i>" => "signal cellular 4 bar",
            "<i class='material-icons'>signal_cellular_connected_no_internet_4_bar</i>" => "signal cellular connected no internet 4 bar",
            "<i class='material-icons'>signal_cellular_no_sim</i>" => "signal cellular no sim",
            "<i class='material-icons'>signal_cellular_null</i>" => "signal cellular null",
            "<i class='material-icons'>signal_cellular_off</i>" => "signal cellular off",
            "<i class='material-icons'>signal_wifi_4_bar</i>" => "signal wifi 4 bar",
            "<i class='material-icons'>signal_wifi_4_bar_lock</i>" => "signal wifi 4 bar lock",
            "<i class='material-icons'>signal_wifi_off</i>" => "signal wifi off",
            "<i class='material-icons'>sim_card</i>" => "sim card",
            "<i class='material-icons'>sim_card_alert</i>" => "sim card alert",
            "<i class='material-icons'>skip_next</i>" => "skip next",
            "<i class='material-icons'>skip_previous</i>" => "skip previous",
            "<i class='material-icons'>slideshow</i>" => "slideshow",
            "<i class='material-icons'>slow_motion_video</i>" => "slow motion video",
            "<i class='material-icons'>smartphone</i>" => "smartphone",
            "<i class='material-icons'>smoke_free</i>" => "smoke free",
            "<i class='material-icons'>smoking_rooms</i>" => "smoking rooms",
            "<i class='material-icons'>sms</i>" => "sms",
            "<i class='material-icons'>sms_failed</i>" => "sms failed",
            "<i class='material-icons'>snooze</i>" => "snooze",
            "<i class='material-icons'>sort</i>" => "sort",
            "<i class='material-icons'>sort_by_alpha</i>" => "sort by alpha",
            "<i class='material-icons'>spa</i>" => "spa",
            "<i class='material-icons'>space_bar</i>" => "space bar",
            "<i class='material-icons'>speaker</i>" => "speaker",
            "<i class='material-icons'>speaker_group</i>" => "speaker group",
            "<i class='material-icons'>speaker_notes</i>" => "speaker notes",
            "<i class='material-icons'>speaker_notes_off</i>" => "speaker notes off",
            "<i class='material-icons'>speaker_phone</i>" => "speaker phone",
            "<i class='material-icons'>spellcheck</i>" => "spellcheck",
            "<i class='material-icons'>star</i>" => "star",
            "<i class='material-icons'>star_border</i>" => "star border",
            "<i class='material-icons'>star_half</i>" => "star half",
            "<i class='material-icons'>stars</i>" => "stars",
            "<i class='material-icons'>stay_current_landscape</i>" => "stay current landscape",
            "<i class='material-icons'>stay_current_portrait</i>" => "stay current portrait",
            "<i class='material-icons'>stay_primary_landscape</i>" => "stay primary landscape",
            "<i class='material-icons'>stay_primary_portrait</i>" => "stay primary portrait",
            "<i class='material-icons'>stop</i>" => "stop",
            "<i class='material-icons'>stop_screen_share</i>" => "stop screen share",
            "<i class='material-icons'>storage</i>" => "storage",
            "<i class='material-icons'>store</i>" => "store",
            "<i class='material-icons'>store_mall_directory</i>" => "store mall directory",
            "<i class='material-icons'>straighten</i>" => "straighten",
            "<i class='material-icons'>streetview</i>" => "streetview",
            "<i class='material-icons'>strikethrough_s</i>" => "strikethrough s",
            "<i class='material-icons'>style</i>" => "style",
            "<i class='material-icons'>subdirectory_arrow_left</i>" => "subdirectory arrow left",
            "<i class='material-icons'>subdirectory_arrow_right</i>" => "subdirectory arrow right",
            "<i class='material-icons'>subject</i>" => "subject",
            "<i class='material-icons'>subscriptions</i>" => "subscriptions",
            "<i class='material-icons'>subtitles</i>" => "subtitles",
            "<i class='material-icons'>subway</i>" => "subway",
            "<i class='material-icons'>supervisor_account</i>" => "supervisor account",
            "<i class='material-icons'>surround_sound</i>" => "surround sound",
            "<i class='material-icons'>swap_calls</i>" => "swap calls",
            "<i class='material-icons'>swap_horiz</i>" => "swap horiz",
            "<i class='material-icons'>swap_vert</i>" => "swap vert",
            "<i class='material-icons'>swap_vertical_circle</i>" => "swap vertical circle",
            "<i class='material-icons'>switch_camera</i>" => "switch camera",
            "<i class='material-icons'>switch_video</i>" => "switch video",
            "<i class='material-icons'>sync</i>" => "sync",
            "<i class='material-icons'>sync_disabled</i>" => "sync disabled",
            "<i class='material-icons'>sync_problem</i>" => "sync problem",
            "<i class='material-icons'>system_update</i>" => "system update",
            "<i class='material-icons'>system_update_alt</i>" => "system update alt",
            "<i class='material-icons'>tab</i>" => "tab",
            "<i class='material-icons'>tab_unselected</i>" => "tab unselected",
            "<i class='material-icons'>tablet</i>" => "tablet",
            "<i class='material-icons'>tablet_android</i>" => "tablet android",
            "<i class='material-icons'>tablet_mac</i>" => "tablet mac",
            "<i class='material-icons'>tag_faces</i>" => "tag faces",
            "<i class='material-icons'>tap_and_play</i>" => "tap and play",
            "<i class='material-icons'>terrain</i>" => "terrain",
            "<i class='material-icons'>text_fields</i>" => "text fields",
            "<i class='material-icons'>text_format</i>" => "text format",
            "<i class='material-icons'>textsms</i>" => "textsms",
            "<i class='material-icons'>texture</i>" => "texture",
            "<i class='material-icons'>theaters</i>" => "theaters",
            "<i class='material-icons'>thumb_down</i>" => "thumb down",
            "<i class='material-icons'>thumb_up</i>" => "thumb up",
            "<i class='material-icons'>thumbs_up_down</i>" => "thumbs up down",
            "<i class='material-icons'>time_to_leave</i>" => "time to leave",
            "<i class='material-icons'>timelapse</i>" => "timelapse",
            "<i class='material-icons'>timeline</i>" => "timeline",
            "<i class='material-icons'>timer</i>" => "timer",
            "<i class='material-icons'>timer_10</i>" => "timer 10",
            "<i class='material-icons'>timer_3</i>" => "timer 3",
            "<i class='material-icons'>timer_off</i>" => "timer off",
            "<i class='material-icons'>title</i>" => "title",
            "<i class='material-icons'>toc</i>" => "toc",
            "<i class='material-icons'>today</i>" => "today",
            "<i class='material-icons'>toll</i>" => "toll",
            "<i class='material-icons'>tonality</i>" => "tonality",
            "<i class='material-icons'>touch_app</i>" => "touch app",
            "<i class='material-icons'>toys</i>" => "toys",
            "<i class='material-icons'>track_changes</i>" => "track changes",
            "<i class='material-icons'>traffic</i>" => "traffic",
            "<i class='material-icons'>train</i>" => "train",
            "<i class='material-icons'>tram</i>" => "tram",
            "<i class='material-icons'>transfer_within_a_station</i>" => "transfer within a station",
            "<i class='material-icons'>transform</i>" => "transform",
            "<i class='material-icons'>translate</i>" => "translate",
            "<i class='material-icons'>trending_down</i>" => "trending down",
            "<i class='material-icons'>trending_flat</i>" => "trending flat",
            "<i class='material-icons'>trending_up</i>" => "trending up",
            "<i class='material-icons'>tune</i>" => "tune",
            "<i class='material-icons'>turned_in</i>" => "turned in",
            "<i class='material-icons'>turned_in_not</i>" => "turned in not",
            "<i class='material-icons'>tv</i>" => "tv",
            "<i class='material-icons'>unarchive</i>" => "unarchive",
            "<i class='material-icons'>undo</i>" => "undo",
            "<i class='material-icons'>unfold_less</i>" => "unfold less",
            "<i class='material-icons'>unfold_more</i>" => "unfold more",
            "<i class='material-icons'>update</i>" => "update",
            "<i class='material-icons'>usb</i>" => "usb",
            "<i class='material-icons'>verified_user</i>" => "verified user",
            "<i class='material-icons'>vertical_align_bottom</i>" => "vertical align bottom",
            "<i class='material-icons'>vertical_align_center</i>" => "vertical align center",
            "<i class='material-icons'>vertical_align_top</i>" => "vertical align top",
            "<i class='material-icons'>vibration</i>" => "vibration",
            "<i class='material-icons'>video_call</i>" => "video call",
            "<i class='material-icons'>video_label</i>" => "video label",
            "<i class='material-icons'>video_library</i>" => "video library",
            "<i class='material-icons'>videocam</i>" => "videocam",
            "<i class='material-icons'>videocam_off</i>" => "videocam off",
            "<i class='material-icons'>videogame_asset</i>" => "videogame asset",
            "<i class='material-icons'>view_agenda</i>" => "view agenda",
            "<i class='material-icons'>view_array</i>" => "view array",
            "<i class='material-icons'>view_carousel</i>" => "view carousel",
            "<i class='material-icons'>view_column</i>" => "view column",
            "<i class='material-icons'>view_comfy</i>" => "view comfy",
            "<i class='material-icons'>view_compact</i>" => "view compact",
            "<i class='material-icons'>view_day</i>" => "view day",
            "<i class='material-icons'>view_headline</i>" => "view headline",
            "<i class='material-icons'>view_list</i>" => "view list",
            "<i class='material-icons'>view_module</i>" => "view module",
            "<i class='material-icons'>view_quilt</i>" => "view quilt",
            "<i class='material-icons'>view_stream</i>" => "view stream",
            "<i class='material-icons'>view_week</i>" => "view week",
            "<i class='material-icons'>vignette</i>" => "vignette",
            "<i class='material-icons'>visibility</i>" => "visibility",
            "<i class='material-icons'>visibility_off</i>" => "visibility off",
            "<i class='material-icons'>voice_chat</i>" => "voice chat",
            "<i class='material-icons'>voicemail</i>" => "voicemail",
            "<i class='material-icons'>volume_down</i>" => "volume down",
            "<i class='material-icons'>volume_mute</i>" => "volume mute",
            "<i class='material-icons'>volume_off</i>" => "volume off",
            "<i class='material-icons'>volume_up</i>" => "volume up",
            "<i class='material-icons'>vpn_key</i>" => "vpn key",
            "<i class='material-icons'>vpn_lock</i>" => "vpn lock",
            "<i class='material-icons'>wallpaper</i>" => "wallpaper",
            "<i class='material-icons'>warning</i>" => "warning",
            "<i class='material-icons'>watch</i>" => "watch",
            "<i class='material-icons'>watch_later</i>" => "watch later",
            "<i class='material-icons'>wb_auto</i>" => "wb auto",
            "<i class='material-icons'>wb_cloudy</i>" => "wb cloudy",
            "<i class='material-icons'>wb_incandescent</i>" => "wb incandescent",
            "<i class='material-icons'>wb_iridescent</i>" => "wb iridescent",
            "<i class='material-icons'>wb_sunny</i>" => "wb sunny",
            "<i class='material-icons'>wc</i>" => "wc",
            "<i class='material-icons'>web</i>" => "web",
            "<i class='material-icons'>web_asset</i>" => "web asset",
            "<i class='material-icons'>weekend</i>" => "weekend",
            "<i class='material-icons'>whatshot</i>" => "whatshot",
            "<i class='material-icons'>widgets</i>" => "widgets",
            "<i class='material-icons'>wifi</i>" => "wifi",
            "<i class='material-icons'>wifi_lock</i>" => "wifi lock",
            "<i class='material-icons'>wifi_tethering</i>" => "wifi tethering",
            "<i class='material-icons'>work</i>" => "work",
            "<i class='material-icons'>wrap_text</i>" => "wrap text",
            "<i class='material-icons'>youtube_searched_for</i>" => "youtube searched for",
            "<i class='material-icons'>zoom_in</i>" => "zoom in",
            "<i class='material-icons'>zoom_out</i>" => "zoom out",
            "<i class='material-icons'>zoom_out_map</i>" => "zoom out map"
        );

        if (isset($icon)) {
            if (isset($data[$icon])) {
                return $data[$icon];
            }
        }

        return $data;
    }
}

if(!function_exists('maskMoneyFormat'))
{
    function maskMoneyFormat($val = 0)
    {
        $result = number_format($val,'2','.',',');
        if(strpos($result,'.00') !== false)
        {
            $result = str_replace('.00','',$result);
        }
        return '$' .$result;
    }
}

if (!function_exists('number_formatter')) {
    function number_formatter($value , $decimals = 2 , $setMoneySign = true){

        $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
        $formatter->setAttribute( $formatter::FRACTION_DIGITS, $decimals );

        if(!$setMoneySign){
            $formatter->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
        }

        return $formatter->format($value);
    }
}

if (!function_exists('get_every_traffic_count')) {
    function get_every_traffic_count($trafficadt = null)
    {
        $data = array('' => '-- Select Traffic Count (ADT) Option --', 'inputvalue' => 'Input a Value', 'Under 2,500' => 'Under 2,500',
            'Unknown' => 'Unknown');
        if (isset($trafficadt)) {
            if (isset($data[$trafficadt])) {
                return $data[$trafficadt];
            }
        }
        return $data;
    }
}

if(!function_exists('is_index_exists'))
{
    function is_index_exists(& $index)
    {
        if(isset($index))
        {
            return $index;
        }
        else
        {
            return 0;
        }
    }
}

if (!function_exists('mail_url')) {
    function mail_url()
    {
        return config_item('mail_url') ? config_item('mail_url') : config_item('base_url');
    }
}
if (!function_exists('get_ada_compliance')) {
    function get_ada_compliance()
    {
        return array('Appears to be compliant'=>'Appears to be compliant','Appears to be non-compliant'=>'Appears to be non-compliant','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_main_structure_base')) {
    function get_main_structure_base()
    {
        return array('Wood Frame'=>'Wood Frame','Pre-Engineered Steel'=>'Pre-Engineered Steel','Concrete Tilt Up Panels'=>'Concrete Tilt Up Panels','Post Beam'=>'Post Beam','Concrete Masonry Units (CMU)'=>'Concrete Masonry Units (CMU)','Structural Insulated Panels (SIPs)'=>'Structural Insulated Panels (SIPs)','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_foundation')) {
    function get_foundation()
    {
        return array('Slab On Grade'=>'Slab On Grade','4" with Standard Footings'=>'4" with Standard Footings','6" with Standard Footings'=>'6" with Standard Footings','8" with Standard Footings'=>'8" with Standard Footings','Standard Footings Only'=>'Standard Footings Only','Helical Piers'=>'Helical Piers','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_parking')) {
    function get_parking()
    {
        return array('Off Street'=>'Off Street','Shared On Street'=>'Shared On Street','Parking Garage'=>'Parking Garage','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_basement')) {
    function get_basement()
    {
        return array('None'=>'None','Partial'=>'Partial','Full'=>'Full','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_exterior')) {
    function get_exterior()
    {
        return array('Wood Siding'=>'Wood Siding','Steel'=>'Steel','Wood'=>'Wood','Glass'=>'Glass','Brick'=>'Brick','Masonry'=>'Masonry','Concrete Block'=>'Concrete Block','Masonry with Block Back Up'=>'Masonry with Block Back Up','Concrete Board and Masonry'=>'Concrete Board and Masonry','Masonry and EFIS exterior'=>'Masonry and EFIS exterior','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_roof')) {
    function get_roof()
    {
        return array('Standing Seam Metal'=>'Standing Seam Metal','60mil TPO Membrane'=>'60mil TPO Membrane','EPDM Membrane'=>'EPDM Membrane','Asphalt Shingle'=>'Asphalt Shingle','Tar and Gravel'=>'Tar and Gravel','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_electrical')) {
    function get_electrical()
    {
        return array('Single Phase'=>'Single Phase','Three Phase'=>'Three Phase','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_plumbing')) {
    function get_plumbing()
    {
        return array('No Plumbing'=>'No Plumbing','Average per Code'=>'Average per Code','Above Average'=>'Above Average','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_heating_cooling')) {
    function get_heating_cooling()
    {
        return array('GFA with Cooling'=>'GFA with Cooling','GFA Blowers'=>'GFA Blowers','Infrared Gas Radiant Tube'=>'Infrared Gas Radiant Tube','HVAC System'=>'HVAC System','Boiler & Chiller System'=>'Boiler & Chiller System','Electric'=>'Electric','In Floor Radiant Heating'=>'In Floor Radiant Heating','No Heating'=>'No Heating','Type My Own'=>'Type My Own');
    }
}
if (!function_exists('get_windows')) {
    function get_windows()
    {
        return array('Insulated Double Pane'=>'Insulated Double Pane','Insulated & Cased'=>'Insulated & Cased','Single Pane'=>'Single Pane','No Windows'=>'No Windows','Mix of Old & New'=>'Mix of Old & New','Type My Own'=>'Type My Own');
    }
}

if (!function_exists('get_comp_types_advance')) {

    function get_comp_types_advance($listing = null)
    {
        $listings = array(
            "sale" => 'Sale',
            "lease" => 'Lease',
            "both"=>'Sale & Lease'
        );

        if (isset($listing) && isset($listings[$listing])) {
            return $listings[$listing];
        }

        return $listings;
    }
}

if (!function_exists('get_comp_type')) {
    function get_comp_type($comp = null)
    {   
        $comps= array(
            'building_with_land' => 'Building(s) with Land',
            'land_only' => 'Land Only'
        );
        if (isset($comp)) {
            if (isset($comps[$comp])) {
                return $comps[$comp];
            } else {
                return null;
            }
        }
        return $comps;
    }
}

if (!function_exists('get_land_type')) {
    function get_land_type($land=null)
    {   
        $lands = array(
            '' => '-- Select Land Type --',
            'office' => 'Office',
            'retail' => 'Retail',
            'retail_pad' => 'Retail Pad',
            'industrial' => 'Industrial',
            'residential' => 'Residential',
            'multi_family' => 'Multifamily',
            'other' => 'Other',
            'ag' => 'Agricultural',
            'Type My Own'=>'Type My Own'
        );
        if (isset($land)) {
            if (isset($lands[$land])) {
                return $lands[$land];
            } else {
                return null;
            }
        }
        return $lands;
    }
}

