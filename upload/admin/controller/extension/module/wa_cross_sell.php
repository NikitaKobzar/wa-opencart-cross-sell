<?php
// SPDX-License-Identifier: GPL-3.0-or-later
class ControllerExtensionModuleWaCrossSell extends Controller {
    public function index() {
        $token = isset($this->request->get['user_token']) ? $this->request->get['user_token'] : '';
        $session_token = isset($this->session->data['user_token']) ? $this->session->data['user_token'] : '';
        if (!$this->user->isLogged() || !is_string($token) || !is_string($session_token) || $session_token === '' || !hash_equals($session_token, $token)) {
            $this->response->addHeader('HTTP/1.1 403 Forbidden');
            $this->response->setOutput('Недійсна сесія адміністратора.');
            return;
        }
        $data = $this->load->language('extension/module/wa_cross_sell');
        $this->document->setTitle($data['heading_title']);
        $this->load->model('setting/module');
        $id = isset($this->request->get['module_id']) ? (int)$this->request->get['module_id'] : 0;
        $stored = array();
        // Never let this route edit a different extension's module instance.
        if ($id) {
            $owner = $this->db->query("SELECT code FROM `" . DB_PREFIX . "module` WHERE module_id = " . $id);
            if (!$owner->num_rows || $owner->row['code'] !== 'wa_cross_sell') {
                $this->response->addHeader('HTTP/1.1 404 Not Found');
                $this->response->setOutput($data['error_module']);
                return;
            }
            $stored = $this->model_setting_module->getModule($id);
        }
        $values = array_merge(array('name' => '', 'title' => 'Доповніть покупку', 'source_id' => '', 'product_ids' => '', 'limit' => 4, 'width' => 240, 'height' => 240, 'status' => 1), $stored ?: array());
        $data['error_warning'] = '';
        if ($this->request->server['REQUEST_METHOD'] === 'POST') {
            foreach ($values as $key => $unused) {
                if (isset($this->request->post[$key]) && is_scalar($this->request->post[$key])) {
                    $values[$key] = trim((string)$this->request->post[$key]);
                }
            }
            // OpenCart escapes request values; store text once, escape at HTML output.
            foreach (array('name', 'title') as $key) { $values[$key] = html_entity_decode($values[$key], ENT_QUOTES, 'UTF-8'); }
            $ids = preg_split('/\s*,\s*/', trim($values['product_ids']));
            $valid = preg_match('/^[1-9][0-9]{0,8}$/D', (string)$values['source_id']) && count($ids) >= 1 && count($ids) <= 50;
            foreach ($ids as $pid) { $valid = $valid && preg_match('/^[1-9][0-9]{0,8}$/D', $pid); }
            foreach (array('limit' => array(1, 12), 'width' => array(40, 1000), 'height' => array(40, 1000)) as $key => $range) {
                $valid = $valid && ctype_digit((string)$values[$key]) && (int)$values[$key] >= $range[0] && (int)$values[$key] <= $range[1];
            }
            $valid = $valid && utf8_strlen($values['name']) >= 3 && utf8_strlen($values['name']) <= 64 && utf8_strlen($values['title']) >= 1 && utf8_strlen($values['title']) <= 100 && in_array((string)$values['status'], array('0', '1'), true);
            if (!$this->user->hasPermission('modify', 'extension/module/wa_cross_sell')) {
                $data['error_warning'] = $data['error_permission'];
            } elseif (!$valid) {
                $data['error_warning'] = $data['error_values'];
            } else {
                $values['product_ids'] = implode(',', array_unique(array_map('intval', $ids)));
                foreach (array('source_id', 'limit', 'width', 'height', 'status') as $key) { $values[$key] = (int)$values[$key]; }
                if ($id) { $this->model_setting_module->editModule($id, $values); }
                else { $this->model_setting_module->addModule('wa_cross_sell', $values); }
                $this->session->data['success'] = $data['text_success'];
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
                return;
            }
        }
        $data = array_merge($data, $values);
        $data['action'] = $this->url->link('extension/module/wa_cross_sell', 'user_token=' . $this->session->data['user_token'] . ($id ? '&module_id=' . $id : ''), true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['products_url'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/wa_cross_sell', $data));
    }
}
