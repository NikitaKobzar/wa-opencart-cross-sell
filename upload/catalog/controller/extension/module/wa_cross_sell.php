<?php
// SPDX-License-Identifier: GPL-3.0-or-later
class ControllerExtensionModuleWaCrossSell extends Controller {
    public function index($setting) {
        if (empty($setting['status']) || empty($setting['source_id']) || empty($this->request->get['product_id']) || (int)$this->request->get['product_id'] !== (int)$setting['source_id'] || !isset($this->request->get['route']) || $this->request->get['route'] !== 'product/product') { return ''; }
        $this->load->model('catalog/product');
        if (!$this->model_catalog_product->getProduct((int)$setting['source_id'])) { return ''; }
        $this->load->model('tool/image');
        $data = $this->load->language('extension/module/wa_cross_sell');
        $data['heading_title'] = isset($setting['title']) ? $setting['title'] : $data['heading_title'];
        $data['products'] = array();
        $limit = max(1, min(12, (int)$setting['limit']));
        $width = max(40, min(1000, (int)$setting['width']));
        $height = max(40, min(1000, (int)$setting['height']));
        $visible_price = $this->customer->isLogged() || !$this->config->get('config_customer_price');
        foreach (array_unique(array_map('intval', explode(',', (string)$setting['product_ids']))) as $id) {
            if ($id <= 0 || $id === (int)$setting['source_id']) { continue; }
            // Native model enforces active status, store, language and availability date.
            $p = $this->model_catalog_product->getProduct($id);
            if (!$p || ((int)$p['quantity'] <= 0 && !$this->config->get('config_stock_checkout'))) { continue; }
            $price = $special = false;
            if ($visible_price) {
                $price = $this->currency->format($this->tax->calculate($p['price'], $p['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                if (isset($p['special']) && $p['special'] !== null && $p['special'] !== false && (float)$p['special'] >= 0) {
                    $special = $this->currency->format($this->tax->calculate($p['special'], $p['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                }
            }
            $data['products'][] = array('product_id' => $id, 'name' => $p['name'], 'image' => $this->model_tool_image->resize($p['image'] ?: 'placeholder.png', $width, $height), 'width' => $width, 'height' => $height, 'price' => $price, 'special' => $special, 'href' => $this->url->link('product/product', 'product_id=' . $id));
            if (count($data['products']) >= $limit) { break; }
        }
        if (!$data['products']) { return ''; }
        $this->document->addStyle('catalog/view/javascript/wa-cross-sell.css');
        return $this->load->view('extension/module/wa_cross_sell', $data);
    }
}
