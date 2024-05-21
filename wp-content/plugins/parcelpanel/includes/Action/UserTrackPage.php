<?php

namespace ParcelPanel\Action;

use ParcelPanel\Api\Api;
use ParcelPanel\Api\RestApi;
use ParcelPanel\Libs\Singleton;
use ParcelPanel\Models\TrackingSettings;
use ParcelPanel\ParcelPanelFunction;
use const ParcelPanel\VERSION;

class UserTrackPage
{
    use Singleton;

    private $order = null;

    private $order_id = 0;

    private $shipment_data = [];

    private $order_number = '';
    private $email = '';
    private $tracking_number = '';
    private $lang = '';

    // Obtain recommended products & product information externally
    function product_message(\WP_REST_Request $request)
    {
        $order_id = !empty($request['order_id']) ? $request['order_id'] : '';
        $email_category = !empty($request['email_category']) ? $request['email_category'] : 0;

        if ($order_id) {
            $productData = $this->get_products_new($order_id);
            $products_category = !empty($productData['products_category']) ? $productData['products_category'] : [];  // Order product category list
            $order_products = !empty($productData['order_products']) ? $productData['order_products'] : [];  // Order product list

            $categoryA = !empty($email_category) ? [$email_category] : $products_category;
            $product = !empty($productData['products']) ? $productData['products'] : [];  // Order product list
            $recommend_products = self::get_recommend_products($categoryA, $order_products);
        }

        $resp_data = [
            'code' => RestApi::CODE_SUCCESS,
            'data' => [
                // 'order_id' => $order_id,
                'product' => !empty($product) ? $product : [],
                'recommend_products' => !empty($recommend_products) ? $recommend_products : [],
            ],
        ];

        return rest_ensure_response($resp_data);
    }

    // Direct access to the page to obtain tracking information
    function track_page_function()
    {
        $this->tracking_number = wc_clean($_GET['nums'] ?? '');
        $this->order_number = wc_clean($_GET['order'] ?? '');
        $this->email = wc_clean($_GET['token'] ?? '');
        // get page lang
        $language = get_bloginfo('language');
        // $current_language = apply_filters( 'wpml_current_language', NULL );
        // var_dump($language);die;
        $this->lang = !empty($language) ? $language : ''; // get page lang

        $tracking_add_key = 'pp-user-track-page-new';
        // GET tracking message
        $trackingData = $this->getInfos();
        $tracking_config = $trackingData['tracking_config'] ?? [];
        $trackingAssetType = !empty($tracking_config['trackingAsset']['type']);
        $trackingAssetUrlJs = !empty($tracking_config['trackingAsset']['js']) ? $tracking_config['trackingAsset']['js'] : '';
        $trackingAssetUrlCss = !empty($tracking_config['trackingAsset']['css']) ? $tracking_config['trackingAsset']['css'] : '';
        $trackingAssetVersion = !empty($tracking_config['trackingAsset']['version']) ? $tracking_config['trackingAsset']['version'] : VERSION;
        // $api_url = 'https://wp.parcelpanel.com/api/v1/wordpress/';
        // $tracking_url = apply_filters('parcelpanel_server_tracking_url', $api_url);
        // $trackingAssetTrackingUrl = !empty($tracking_config['trackingAsset']['tracking_url']) ? $tracking_config['trackingAsset']['tracking_url'] : $tracking_url;
        unset($tracking_config['trackingAsset']);
        if ($trackingAssetType) {
            if ($trackingAssetUrlCss) {
                wp_register_style($tracking_add_key, $trackingAssetUrlCss, [], $trackingAssetVersion);
                wp_enqueue_style($tracking_add_key);
            }
            if ($trackingAssetUrlJs) {
                wp_register_script($tracking_add_key, $trackingAssetUrlJs, [], $trackingAssetVersion, true);
                wp_enqueue_script($tracking_add_key);
            }
        } else {
            // wp_register_style($tracking_add_key, (new ParcelPanelFunction)->parcelpanel_get_assets_path("tracking/index.css"), [], VERSION);
            // wp_enqueue_style($tracking_add_key);
            wp_register_script($tracking_add_key, (new ParcelPanelFunction)->parcelpanel_get_assets_path("tracking/index.js"), [], VERSION, true);
            wp_enqueue_script($tracking_add_key);
        }

        $parse_url = parse_url(home_url());
        $domain = $parse_url['host'] ?? '';

        $PP_Token = Api::get_api_key();
        $bid = Api::get_bid();
        $authorization = base64_encode($domain . ',' . $bid . ',' . md5($PP_Token));

        $pp_tracking_params = [
            'domain' => $domain,
            'authorization' => $authorization,
            'action' => 'pp_tracking_info',
            'ajax_url' => admin_url('admin-ajax.php', 'relative'),
            'get_track_info_nonce' => wp_create_nonce('pp-track-info-get'),
            // 'tracking_url' => $trackingAssetTrackingUrl,
        ];
        wp_localize_script($tracking_add_key, 'pp_tracking_params', $pp_tracking_params);

        ob_start();

        $weglotIsActivePlugins = is_plugin_active('weglot/weglot.php'); // weglot is active
        if ($weglotIsActivePlugins) {
            // weglot set data
            $weglotTran = [];
            $weglotTran['translate'] = !empty($tracking_config['languages']['translate']) ? $tracking_config['languages']['translate'] : [];
            $tracking_config_weglot = json_encode($weglotTran);
            $weglot_config = "window.pp_track_weglot = {$tracking_config_weglot};";
            wp_add_inline_script($tracking_add_key, $weglot_config, 'before');
        } else {
            $weglotTran = [];
            $weglotTran['translate'] = null;
            $tracking_config_weglot = json_encode($weglotTran);
            $weglot_config = "window.pp_track_weglot = {$tracking_config_weglot};";
            wp_add_inline_script($tracking_add_key, $weglot_config, 'before');
        }

        $tracking_data_str = json_encode($trackingData);
        $js = "window.pp_tracking_data = {$tracking_data_str};";
        wp_add_inline_script($tracking_add_key, $js, 'before');

        $style = '
            #pp-root .pp-loading-container {
            margin: 60px auto 0;
            width: 150px;
            }
            #pp-root .pp-loading-container .loading {
            display: flex;
            height: 80px;
            justify-content: space-around;
            align-items: center;
            }
            #pp-root .pp-loading-container span {
            width: 10px;
            height: 10px;
            background-color: #e6e6e6;
            border-radius: 50%;
            display: inline-block;
            line-height: 80px;
            animation: loading 2s infinite ease;
            -webkit-animation: loading 2s infinite ease;
            }
            #pp-root .pp-loading-container span:nth-child(1) {
            animation-delay: 0s;
            -webkit-animation-delay: 0s;
            }
            #pp-root .pp-loading-container span:nth-child(2) {
            animation-delay: 0.2s;
            -webkit-animation-delay: 0.2s;
            }
            #pp-root .pp-loading-container span:nth-child(3) {
            animation-delay: 0.4s;
            -webkit-animation-delay: 0.4s;
            }
            #pp-root .pp-loading-container span:nth-child(4) {
            animation-delay: 0.6s;
            -webkit-animation-delay: 0.6s;
            }
            #pp-root .pp-loading-container span:nth-child(5) {
            animation-delay: 0.8s;
            -webkit-animation-delay: 0.8s;
            }
            #pp-root .pp-loading-container span:nth-child(6) {
            animation-delay: 1s;
            -webkit-animation-delay: 1s;
            }
            @keyframes loading {
            0% {
                transform: scale(1);
            }
            20% {
                transform: scale(2.5);
            }
            40% {
                transform: scale(1);
            }
            }
        ';

        echo '<style>' . strip_tags($style) . '</style>';

        // $str = '<div id="pp-root" style="max-width: 1200px;"></div>';
        $str = '<div id="pp-root"><div class="pp-loading-container">
            <div class="loading">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            </div>
        </div></div>';
        echo wp_kses_post($str);
        return ob_get_clean();
    }

    // tracking info data get click
    function get_track_info_new_ajax()
    {
        $this->tracking_number = wc_clean($_POST['nums'] ?? '');
        $this->order_number = wc_clean($_POST['order'] ?? '');
        $this->email = wc_clean($_POST['token'] ?? '');
        $this->lang = wc_clean($_POST['lang'] ?? ''); // get page lang
        if (empty($this->lang)) {
            $language = get_bloginfo('language');
            $this->lang = !empty($language) ? $language : ''; // get page lang
        }

        $rtn = $this->getInfos();
        (new ParcelPanelFunction)->parcelpanel_json_response($rtn);
    }

    // GET tracking message
    function getInfos()
    {

        // theme data
        $current_theme = wp_get_theme();
        // $theme_name = $current_theme->get('Name');
        // $theme_version = $current_theme->get('Version');
        // $theme_author = $current_theme->get('Author');
        $theme_code = $current_theme->get('TextDomain');

        $trackingData = [
            'tracking_other' => [],
            'tracking_config' => [],
            'tracking_data' => [],
        ];

        // get tracking configs
        $params = [
            'order' => $this->order_number ?? '',
            'token' => $this->email ?? '',
            'nums' => $this->tracking_number,
            'lang' => $this->lang,
            'theme_code' => $theme_code,
            // 'config' => 1, // only get configs
        ];
        $trackingData = $this->get_pp_api_tracking($params);

        $tracking_config = $trackingData['tracking_config'] ?? [];
        $tracking_data = $trackingData['tracking_data'] ?? [];
        $pluginsTagger = $trackingData['pluginsTagger'] ?? [];
        unset($trackingData['pluginsTagger']);

        if (empty($tracking_data)) {
            return $trackingData;
        }

        // translate wpml open change text to default
        $isActivePlugins = is_plugin_active('wpml-string-translation/plugin.php'); // check wpml is active
        $isActiveCMSPlugins = is_plugin_active('sitepress-multilingual-cms/sitepress.php'); // check wpml cms is active
        if (!empty($pluginsTagger) && !empty($pluginsTagger['wpml']) && !empty($isActivePlugins) && !empty($isActiveCMSPlugins)) {
            $changeLang = $this->getChangeLang();
            $tracking_page_translations = !empty($tracking_config['languages']['translate']) ? $tracking_config['languages']['translate'] : [];
            $changeLang = $this->getTranWPMLNew($tracking_page_translations);
            $tracking_config['languages']['translate'] = $changeLang;
            $trackingData['tracking_config'] = $tracking_config;
            $tracking_data = $this->changeTrackingInfo($tracking_data, $tracking_page_translations, $changeLang);
        }

        $order_id = $tracking_data['order_id'] ?? 0;
        $products_category = [];
        $order_products = [];
        if ($order_id) {
            $productData = $this->get_products_new($order_id);
            $products_category = $productData['products_category'] ?? [];  // pro category
            $order_products = $productData['order_products'] ?? [];  // pro list
            $tracking_data['product'] = $productData['products'];  // order pro list
            $tracking_data = $this->upTrackingProductNew($tracking_data);
            unset($tracking_data['product']);
        }

        // add recommend_products
        if (empty($tracking_data['recommend_products'])) {
            $tracking_data['recommend_products'] = self::get_recommend_products_new($products_category, $order_products, $tracking_config);
        } else {
            // priview recommend_products
            foreach ($tracking_data['recommend_products'] as &$pro) {
                $price_html = $pro['price_html'] ?? 0;
                $pro['price_html'] = wc_price($price_html);
            }
        }

        $trackingData['tracking_data'] = $tracking_data;

        return $trackingData;
    }

    // pp get translate
    function getTranOther(\WP_REST_Request $request)
    {
        $dataRequest = !empty($request['dataRequest']) ? $request['dataRequest'] : [];

        if (empty($dataRequest)) {
            $resp_data = [
                'code' => RestApi::CODE_SUCCESS,
                'data' => [],
            ];
            return rest_ensure_response($resp_data);
        }

        $pluginsTagger = !empty($data_request['pluginsTagger']) ? $data_request['pluginsTagger'] : [];

        $changeLang = [];
        $changeLangWC = [];
        // translate wpml open change text to default
        $isActivePlugins = is_plugin_active('wpml-string-translation/plugin.php'); // check wpml is active
        $isActiveCMSPlugins = is_plugin_active('sitepress-multilingual-cms/sitepress.php'); // check wpml cms is active
        if (!empty($pluginsTagger) && !empty($pluginsTagger['wpml']) && !empty($isActivePlugins) && !empty($isActiveCMSPlugins)) {
            $tracking_page_translations = !empty($dataRequest['languages']['translate']) ? $dataRequest['languages']['translate'] : [];
            $changeLang = $this->getTranWPMLNew($tracking_page_translations);

            foreach ($changeLang as $k => $v) {
                $changeLangWC[$k] = esc_html__($v, 'pancelpanel');
            }
        }

        $resp_data = [
            'code' => RestApi::CODE_SUCCESS,
            'data' => [
                'changeLang' => $changeLang,
                'changeLangWC' => $changeLangWC,
            ],
        ];

        return rest_ensure_response($resp_data);
    }

    // pp get pro message
    function getTrackingPro(\WP_REST_Request $request)
    {
        $dataRequest = !empty($request['dataRequest']) ? $request['dataRequest'] : [];

        if (empty($dataRequest)) {
            $resp_data = [
                'code' => RestApi::CODE_SUCCESS,
                'data' => [],
            ];
            return rest_ensure_response($resp_data);
        }

        $order_id = $dataRequest['order_id'] ?? 0;
        $products_category = [];
        $order_products = [];
        if ($order_id) {
            $productData = $this->get_products_new($order_id);
            $products_category = $productData['products_category'] ?? [];  // pro category
            $order_products = $productData['order_products'] ?? [];  // pro list
            $orderPro = $productData['products'] ?? [];
            $checkPro = [];
            $checkProVar = [];
            foreach ($orderPro as $v) {
                $pro_id = $v['pro_id'] ?? '';
                $checkPro[$pro_id] = $v;
                $checkProVar[$pro_id] = $v;
            }
        }

        $recommend_products = !empty($dataRequest['recommend_products']) ? $dataRequest['recommend_products'] : [];

        // get recommend_products
        if (empty($dataRequest['preview'])) {
            $recommend_products = self::get_recommend_products_new($products_category, $order_products, $dataRequest);
        } else {
            // priview recommend_products
            foreach ($recommend_products as &$pro) {
                $price_html = $pro['price_html'] ?? 0;
                $pro['price_html'] = wc_price($price_html);
            }
        }

        $resp_data = [
            'code' => RestApi::CODE_SUCCESS,
            'data' => [
                'recommend_products' => $recommend_products,
            ],
        ];

        return rest_ensure_response($resp_data);
    }

    // 更新发货产品信息
    private function upTrackingProductNew($tracking_data): array
    {
        $tracking = $tracking_data['tracking'] ?? [];
        $orderPro = $tracking_data['product'] ?? [];
        $checkPro = [];
        $checkProVar = [];
        foreach ($orderPro as $v) {
            $pro_id = $v['pro_id'] ?? '';
            $checkPro[$pro_id] = $v;
            $checkProVar[$pro_id] = $v;
        }
        foreach ($tracking as $k => $v) {
            $newProduct = [];
            $product = $v['product'] ?? [];
            foreach ($product as $vv) {
                $pro_id = $vv['id'] ?? 0;
                $var_id = $vv['var_id'] ?? 0;
                $quantity = $vv['quantity'] ?? 0;
                if (!empty($checkPro[$pro_id])) {
                    $checkPro[$pro_id]['quantity'] = $quantity;
                    $newProduct[] = $checkPro[$pro_id];
                } else if (!empty($checkProVar[$var_id])) {
                    $checkProVar[$var_id]['quantity'] = $quantity;
                    $newProduct[] = $checkProVar[$var_id];
                }
            }
            if (!empty($newProduct)) {
                $tracking_data['tracking'][$k]['product'] = $newProduct;
            }
        }

        return $tracking_data;
    }

    private static function get_recommend_products_new($products_category = [], $order_products = [], $tracking_config = []): array
    {
        // 推荐 app 集合
        $recommend_products = [];

        // get products
        $PRODUCT_RECOMMEND = !empty($tracking_config['productRec']) ? $tracking_config['productRec'] : [];
        $base_pro = !empty($PRODUCT_RECOMMEND['base_pro']) ? $PRODUCT_RECOMMEND['base_pro'] : 1;
        $PRODUCT_RECOMMEND_advanced = $base_pro == 2; // 1 : Based on order items   2: Based on specific category
        $PRODUCT_RECOMMEND_CAT_ID = $PRODUCT_RECOMMEND['product_cat_id'];
        if (!empty($PRODUCT_RECOMMEND_advanced) && !empty($PRODUCT_RECOMMEND_CAT_ID)) {
            $back = self::get_recommend_products_by_cate_ids($recommend_products, $order_products, $PRODUCT_RECOMMEND_CAT_ID);
            return $back['recommend_products'] ?? [];
        }

        // 不存在分类直接返回空
        if (empty($products_category)) {
            return [];
        }

        // 获取所有分层
        $category_all = get_terms(
            array(
                'taxonomy' => 'product_cat',
                'pad_counts' => false,
                'hide_empty' => false,
                // 'include'  => $products_category, // 获取对应产品分类的分类列表
                // 'fields'   => 'names',
            )
        );
        $cate_all = self::getProCate($category_all, $products_category);
        $cate_ids = $cate_all['cate_ids'] ?? []; // 分类不同层级分类id
        $cate_lv = $cate_all['lv'] ?? 0; // 分类层级
        // print_r($cate_ids);
        // print_r($category_names);die;
        // 取后三个层级的分类 id 获取推荐产品
        $first_cate_ids = $cate_ids[$cate_lv - 1] ?? [];
        $second_cate_ids = $cate_ids[$cate_lv - 2] ?? [];
        $third_cate_ids = $cate_ids[$cate_lv - 3] ?? [];
        // $all_cate_ids = array_merge($first_cate_ids, $second_cate_ids, $third_cate_ids);
        $get_pro = [];
        $get_pro[] = $first_cate_ids;
        $get_pro[] = $second_cate_ids;
        $get_pro[] = $third_cate_ids;
        foreach ($get_pro as $v) {
            if (!empty($v)) {
                $back = self::get_recommend_products_by_cate_ids($recommend_products, $order_products, $v);
                $recommend_products = $back['recommend_products'] ?? [];
                $order_products = $back['order_products'] ?? [];
            }
        }
        return $recommend_products;
    }

    // new tracking data
    function get_pp_api_tracking($params)
    {
        // get tracking info
        $trackingMessage = Api::userTrackingPageNew($params);
        if (is_wp_error($trackingMessage)) {
            return [];
        }

        return $trackingMessage['data'] ?? [];
    }

    // get WPML translate tracking page
    function getTranWPMLNew($tracking_page_translations)
    {
        $changeLang = $this->getChangeLang();
        $tracking_page_translations = $this->changeTrackingTranslateNew($tracking_page_translations, $changeLang);
        return $tracking_page_translations;
    }
    // tracking str translate to
    public function changeTrackingTranslateNew($tracking_page_translations, $changeLang)
    {
        $checkText = [
            "additional_text_above",
            "additional_text_below",
            "custom_shipment_status_name_1",
            "custom_shipment_status_info_1",
            "custom_shipment_status_name_2",
            "custom_shipment_status_info_2",
            "custom_shipment_status_name_3",
            "custom_shipment_status_info_3",
            "custom_tracking_info",
        ];
        if (!empty($tracking_page_translations)) {
            $tracking_page_translations_new = [];
            foreach ($tracking_page_translations as $k => $v) {
                if (!isset($changeLang[$k])) {
                    continue;
                }
                $tranLangStr = esc_html__($changeLang[$k], 'pancelpanel');
                if ($tranLangStr == $changeLang[$k] && in_array($k, $checkText)) {
                    $tracking_page_translations_new[$k] = $v;
                    continue;
                }
                $tracking_page_translations_new[$k] = $tranLangStr;
            }
            $tracking_page_translations = $tracking_page_translations_new;
        }
        return $tracking_page_translations;
    }

    // get WPML translate tracking page
    function getTranWPML($tracking_config)
    {
        $changeLang = $this->getChangeLang();
        $tracking_page_translations = !empty($tracking_config['tracking_page_translations']) ? $tracking_config['tracking_page_translations'] : [];
        $tracking_config = $this->changeTrackingTranslate($tracking_config, $tracking_page_translations, $changeLang);

        return [
            'changeLang' => $changeLang,
            'tracking_page_translations' => $tracking_page_translations,
            'tracking_config' => $tracking_config,
        ];
    }

    // tracking str translate to
    public function changeTrackingTranslate($tracking_config, $tracking_page_translations, $changeLang)
    {
        if (!empty($tracking_page_translations)) {
            $tracking_page_translations_new = [];
            foreach ($tracking_page_translations as $k => $v) {
                if (isset($changeLang[$k])) {
                    $tracking_page_translations_new[$k] = esc_html__($changeLang[$k], 'pancelpanel');
                }
            }
            $tracking_config['tracking_page_translations'] = $tracking_page_translations_new;
        }
        return $tracking_config;
    }

    // wpml tran get
    private function getChangeLang()
    {
        return [
            "order_number" => "Order Number",
            "email" => "Email or Phone Number",
            "or" => "Or",
            "tracking_number" => "Tracking Number",
            "track" => "Track",
            "order" => "Order",
            "status" => "Status",
            "shipping_to" => "Shipping To",
            "current_location" => "Current Location",
            "carrier" => "Carrier",
            "product" => "Product",
            "not_yet_shipped" => "These items have not yet shipped.",
            "waiting_updated" => "Waiting for carrier to update tracking information, please try again later.",
            "ordered" => "Ordered",
            "order_ready" => "Order Ready",
            "pending" => "Pending",
            "info_received" => "Info Received",
            "in_transit" => "In Transit",
            "out_for_delivery" => "Out for Delivery",
            "delivered" => "Delivered",
            "exception" => "Exception",
            "failed_attempt" => "Failed Attempt",
            "expired" => "Expired",
            "expected_delivery" => "Estimated delivery date",
            "may_like" => "You may also like...",

            // test text
            "additional_text_above" => "Additional text above",
            "additional_text_below" => "Additional text below",
            "custom_shipment_status_name_1" => "Custom shipment status name 1",
            "custom_shipment_status_info_1" => "Custom shipment status info 1",
            "custom_shipment_status_name_2" => "Custom shipment status name 2",
            "custom_shipment_status_info_2" => "Custom shipment status info 2",
            "custom_shipment_status_name_3" => "Custom shipment status name 3",
            "custom_shipment_status_info_3" => "Custom shipment status info 3",
            "custom_tracking_info" => "Custom tracking info",

            "order_not_found" => "Could Not Find Order",
            "enter_your_order" => "Please enter your order number",
            "enter_your_email" => "Please enter your email or phone number",
            "enter_your_tracking_number" => "Please enter your tracking number",
        ];
    }

    // tracking info translate to
    private function changeTrackingInfo($tracking_data, $tracking_page_translations, $changeLang)
    {

        $arrTo = [
            'custom_shipment_status_info_1' => 'custom_shipment_status_name_1',
            'custom_shipment_status_info_2' => 'custom_shipment_status_name_2',
            'custom_shipment_status_info_3' => 'custom_shipment_status_name_3',
        ];

        $defaultA = [
            "additional_text_above" => "Additional text above",
            "additional_text_below" => "Additional text below",
            "custom_shipment_status_name_1" => "Custom shipment status name 1",
            "custom_shipment_status_info_1" => "Custom shipment status info 1",
            "custom_shipment_status_name_2" => "Custom shipment status name 2",
            "custom_shipment_status_info_2" => "Custom shipment status info 2",
            "custom_shipment_status_name_3" => "Custom shipment status name 3",
            "custom_shipment_status_info_3" => "Custom shipment status info 3",
            "custom_tracking_info" => "Custom tracking info",
        ];

        $tracking = $tracking_data['tracking'] ?? [];

        foreach ($tracking as &$track) {

            if (!empty($track['status_node'])) {
                foreach ($track['status_node'] as &$node) {
                    $name = $node['name'] ?? '';
                    $key_tran = $this->checkTranKey($tracking_page_translations, $name);
                    if (empty($key_tran)) {
                        continue;
                    }
                    $wp_r = esc_html__($changeLang[$key_tran], 'pancelpanel');
                    if ($wp_r != $changeLang[$key_tran]) {
                        $node['name'] = $wp_r;
                    }
                }
            }

            $status_num_name = !empty($track['status_num']['name']) ? $track['status_num']['name'] : '';
            $status_num_name_key = $this->checkTranKey($tracking_page_translations, $status_num_name);
            if (!empty($status_num_name_key)) {
                $wp_r = esc_html__($changeLang[$status_num_name_key], 'pancelpanel');
                if ($wp_r != $changeLang[$status_num_name_key]) {
                    $track['status_num']['name'] = $wp_r;
                }
            }

            $status_num_name_d = !empty($track['status_num']['status_description']) ? $track['status_num']['status_description'] : '';
            $status_num_name_d_key = $this->checkTranKey($tracking_page_translations, $status_num_name_d);
            if (!empty($status_num_name_d_key)) {
                $wp_r = esc_html__($changeLang[$status_num_name_d_key], 'pancelpanel');
                if ($wp_r != $changeLang[$status_num_name_key]) {
                    $track['status_num']['status_description'] = $wp_r;
                }
            }

            $status_n = !empty($track['status']) ? $track['status'] : '';
            $status_k = $this->checkTranKey($tracking_page_translations, $status_n);
            if (!empty($status_k)) {
                $wp_r = esc_html__($changeLang[$status_k], 'pancelpanel');
                if ($wp_r != $changeLang[$status_k]) {
                    $track['status'] = $wp_r;
                }
            }

            if (!empty($track['trackinfo'])) {
                $trackinfo = $track['trackinfo'];
                foreach ($trackinfo as $kk => $vv) {
                    if (!empty($vv['name_key'])) {
                        $name_K = $arrTo[$vv['name_key']] ?? '';
                        $wpml_text = $changeLang[$vv['name_key']] ?? '';
                        if (empty($wpml_text)) {
                            $wpml_text = $changeLang[$name_K] ?? '';
                        }

                        $check_a = [];
                        $check_t = $defaultA[$vv['name_key']] ?? '';
                        $check_t1 = $defaultA[$name_K] ?? '';
                        $check_a[] = $check_t;
                        $check_a[] = $check_t1;

                        if (!empty($wpml_text) && !in_array($wpml_text, $check_a)) {
                            $trackinfo[$kk]['status_description'] = $wpml_text;
                        }
                    }
                }
                $track['trackinfo'] = $trackinfo;
            }
        }

        if (!empty($tracking)) {
            $tracking_data['tracking'] = $tracking;
        }

        return $tracking_data;
    }

    private function checkTranKey($tracking_page_translations, $name)
    {
        $key_str = '';
        if (empty($name)) {
            return $key_str;
        }
        foreach ($tracking_page_translations as $k => $v) {
            if ($v == $name) {
                $key_str = $k;
                break;
            }
        }
        return $key_str;
    }

    /**
     * Adds data to the custom "Track" column in "My Account > Orders".
     *
     * @param \WC_Order $order the order object for the row
     */
    public function add_column_my_account_orders_pp_track_column($actions, \WC_Order $order)
    {
        $TRACK_BUTTON_ORDER_STATUS = AdminSettings::get_track_button_order_status_field();
        $TRACKING_SETTINGS = \ParcelPanel\Models\TrackingSettings::instance()->get_settings();

        $TRANSLATIONS = $TRACKING_SETTINGS['tracking_page_translations'];

        // 启用状态
        $is_enable_track = AdminSettings::get_orders_page_add_track_button_field();

        $order_status = '';
        if (is_a($order, 'WC_Order')) {
            $order_status = $order->get_status() ?? '';
        }
        $_sync_status = $order->get_meta('_parcelpanel_sync_status');

        if (!$is_enable_track || (!in_array($order_status, $TRACK_BUTTON_ORDER_STATUS, true) && !in_array("wc-{$order_status}", $TRACK_BUTTON_ORDER_STATUS, true))) {
            return $actions;
        }

        $order_number = $order->get_order_number();
        $email = $order->get_billing_email();
        // if ( empty( $email ) ) {
        //     $user = wp_get_current_user();
        //     $email = $user->user_email;
        // }

        $track_url = (new ParcelPanelFunction)->parcelpanel_get_track_page_url(false, "#{$order_number}", $email);

        if (empty($track_url)) {
            return $actions;
        }

        $actions['pp-track'] = [
            'url' => $track_url,
            'name' => $TRANSLATIONS['track'],
        ];

?>
        <script>
            jQuery(document).ready(function() {
                jQuery('.pp-track').attr('target', '_blank')
            })
        </script>
<?php

        return $actions;
    }

    static function encode_email($email)
    {
        if (false === strpos($email, '@')) {
            return $email;
        }

        $email = str_replace('@', '_-_', $email);

        return strrev($email);
    }

    static function decode_email($email)
    {
        if (false === strpos($email, '_-_')) {
            return $email;
        }

        $email = str_replace('_-_', '@', $email);

        return sanitize_email(strrev($email));
    }

    private function get_products_new($order_id)
    {
        if (empty($order_id)) {
            return [];
        }

        $order = wc_get_order($order_id);

        // + 商品列表
        $products = [];

        // + 商品分类列表
        $products_category = [];

        // 订单中的产品
        $order_products = [];

        if (!empty($order)) {
            /* @var \WC_Order_Item_Product $item */
            foreach ($order->get_items() as $item) {

                /* @var \WC_Product $product */
                $product = $item->get_product();

                if (empty($product)) {
                    continue;
                }

                $category_ids = $product->get_category_ids();
                if (!empty($category_ids)) {
                    foreach ($category_ids as $v) {
                        if (!in_array($v, $products_category)) {
                            $products_category[] = $v;
                        }
                    }
                }

                $permalink = get_permalink($product->get_id());

                $image = wp_get_attachment_url($product->get_image_id()) ?: '';

                $order_products[] = $item->get_name();

                $pro_now_id = $product->get_id();
                $parent_id = !empty($product->get_parent_id()) ? $product->get_parent_id() : 0;
                $link_pro = $parent_id ? $parent_id : $pro_now_id;

                $products[] = [
                    'pro_id' => $product->get_id(),
                    'id' => $item->get_id(),
                    'name' => $item->get_name(),
                    'sku' => $product->get_sku(),
                    'quantity' => $item->get_quantity(),
                    'image_url' => $image,
                    'link' => self::getProductGetParam($permalink, $link_pro),
                ];
            }
        }

        return [
            'products' => $products,
            'products_category' => $products_category,
            'order_products' => $order_products,
        ];
    }

    // get product get params 
    // type : recommend_product product
    public static function getProductGetParam($permalink, $productId, $type = "product", $from = "tracking_page")
    {
        $link_res = explode('?', $permalink);
        $link = !empty($link_res) ? $link_res[0] : $permalink;
        $baseUrl = rest_url('parcelpanel/v1/');
        $domain = parse_url($baseUrl, PHP_URL_HOST);
        return $link . "?ref=parcelpanel&utm_source=parcelpanel&utm_medium=" . $from . "&utm_campaign=" . $type . "&pp_product=" . $productId . "&domain=" . $domain;
    }

    // Get all level arrays of product categories
    private static function getProCate($category_all, $products_category)
    {
        if (empty($products_category)) {
            return [];
        }

        $cate_all = [];
        $cate_arr = [];
        foreach ($category_all as $v) {
            $cate_arr[$v->parent][] = $v->term_id;
        }

        $cate_all[0] = $cate_arr[0] ?? [];
        unset($cate_arr[0]);

        $res = self::getCateLv($cate_all[0], $cate_all, $cate_arr);

        $now_cate_arr = []; // There are corresponding levels for product categories
        foreach ($products_category as $v) {
            foreach ($res as $k => $vv) {
                if (in_array($v, $vv)) {
                    $now_cate_arr[$k][] = $v;
                }
            }
        }
        sort($now_cate_arr);

        return [
            "cate_ids" => $now_cate_arr,
            "lv" => count($now_cate_arr),
        ];
    }

    private static function getCateLv($parent, $nowCateArr, $cate_arr, $lv = 1)
    {

        if (empty($cate_arr)) {
            return $nowCateArr;
        }

        foreach ($cate_arr as $k => $v) {
            if (in_array($k, $parent)) {
                foreach ($v as $vv) {
                    $nowCateArr[$lv][] = $vv;
                }
                unset($cate_arr[$k]);
            }
        }
        $parent = $nowCateArr[$lv];
        $lv++;

        if (!empty($cate_arr) && count($nowCateArr) != $lv) {
            $lv = $lv - 1;
            // Add the existing ones to the second and third layers
            foreach ($cate_arr as $k => $v) {
                if (!empty($nowCateArr[$lv - 1])) {
                    foreach ($v as $vv) {
                        $nowCateArr[$lv - 1][] = $vv;
                    }
                }
                if (!empty($nowCateArr[$lv - 2])) {
                    $nowCateArr[$lv - 2][] = $k;
                }
            }

            return $nowCateArr;
        }

        return self::getCateLv($parent, $nowCateArr, $cate_arr, $lv);
    }

    private static function get_recommend_products($products_category = [], $order_products = [], $advanced = 0): array
    {
        // Recommended app collection
        $recommend_products = [];

        // Get the product category set by the user
        $PRODUCT_RECOMMEND = TrackingSettings::instance()->product_recommend;
        $PRODUCT_RECOMMEND_advanced = $PRODUCT_RECOMMEND['advanced'] ?? false;
        $PRODUCT_RECOMMEND_CAT_ID = $PRODUCT_RECOMMEND['product_cat_id'];
        if (!empty($PRODUCT_RECOMMEND_advanced) && !empty($PRODUCT_RECOMMEND_CAT_ID)) {
            $back = self::get_recommend_products_by_cate_ids($recommend_products, $order_products, $PRODUCT_RECOMMEND_CAT_ID);
            return $back['recommend_products'] ?? [];
        }

        // If there is no category, return empty directly.
        if (empty($products_category)) {
            return [];
        }

        // Get all layers
        $category_all = get_terms(
            array(
                'taxonomy' => 'product_cat',
                'pad_counts' => false,
                'hide_empty' => false,
                // 'include'  => $products_category, // 获取对应产品分类的分类列表
                // 'fields'   => 'names',
            )
        );
        $cate_all = self::getProCate($category_all, $products_category);
        $cate_ids = $cate_all['cate_ids'] ?? []; // Classification IDs at different levels of classification
        $cate_lv = $cate_all['lv'] ?? 0; // Classification level
        // print_r($cate_ids);
        // print_r($category_names);die;
        // Get the category IDs of the last three levels to get recommended products
        $first_cate_ids = $cate_ids[$cate_lv - 1] ?? [];
        $second_cate_ids = $cate_ids[$cate_lv - 2] ?? [];
        $third_cate_ids = $cate_ids[$cate_lv - 3] ?? [];
        // $all_cate_ids = array_merge($first_cate_ids, $second_cate_ids, $third_cate_ids);
        $get_pro = [];
        $get_pro[] = $first_cate_ids;
        $get_pro[] = $second_cate_ids;
        $get_pro[] = $third_cate_ids;
        foreach ($get_pro as $v) {
            if (!empty($v)) {
                $back = self::get_recommend_products_by_cate_ids($recommend_products, $order_products, $v);
                $recommend_products = $back['recommend_products'] ?? [];
                $order_products = $back['order_products'] ?? [];
            }
        }
        return $recommend_products;
    }

    // get recommend_products list
    private static function get_recommend_products_by_cate_ids($recommend_products, $order_products, $cateIds)
    {
        // recommend_products count
        $count_pro = count($order_products) + 20;

        $query_args = [
            'fields' => 'ids',
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $count_pro,
            'tax_query' => [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $cateIds, // array( 'jazz', 'improv' )
                ],
            ],
            // 'orderby' => 'date', // sort
            // 'order' => 'DESC',   // sort
        ];

        $WP_Query = new \WP_Query($query_args);
        foreach ($WP_Query->posts as $product_id) {
            $product = wc_get_product($product_id);
            if (in_array($product->get_name(), $order_products)) {
                // 排除订单中的产品 $order_products
                continue;
            }

            $attachment = wp_get_attachment_image_src($product->get_image_id(), 'full');
            if (is_array($attachment)) {
                $src = current($attachment);
            } else {
                $src = wc_placeholder_img_src();
            }

            $pro_now_id = $product->get_id();
            $parent_id = !empty($product->get_parent_id()) ? $product->get_parent_id() : 0;
            $link_pro = $parent_id ? $parent_id : $pro_now_id;

            $quantity = $product->get_stock_quantity();
            $quantity_status = $product->get_stock_status();
            // $backorders = $product->get_backorders(); // no notify yes
            // ($quantity_status != 'instock' && $quantity_status != 'onbackorder')
            if ($quantity === 0 || $quantity_status != 'instock') {
                continue;
            }

            $recommend_products[] = [
                'title' => $product->get_name(),
                'price_html' => wc_price($product->get_price()),
                'url' => self::getProductGetParam($product->get_permalink(), $link_pro, "recommend_product"),
                'img' => $src,
            ];
            $order_products[] = $product->get_name();
            if (count($recommend_products) == 10) {
                break;
            }
        }

        return [
            'order_products' => $order_products,
            'recommend_products' => $recommend_products
        ];
    }
}
