<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 *
 * Class Jambi
 *
 */
class Jambi
{
    /**
     * Loader class instance
     * @var object $Loader
     */
    public $Loader;

    /**
     * DbSync class instance
     * @var object $DbSync
     */
    public $DbSync;

    /**
     * Member class instance
     * @var object $Member
     */
    public $Member;

    /**
     * Login class instance
     * @var object $Login
     */
    public $Login;

    /**
     * @var $table
     */
    public $table;

    /**
     * @var $get
     */
    public $get;

    /**
     * @var $data
     */
    public $data;

    /**
     * @var $settings
     */
    public $settings;

    /**
     * Jambi constructor.
     *
     * @param $Loader Loader object
     */
    public function __construct(Loader $Loader)
    {
        // Assign $Loader object
        $this->Loader = $Loader;

        // Create new instance of DbSync class
        $this->DbSync = new DbSync($this->Loader);

        // Create new instance of Member class.
        $this->Member = new Member($this->Loader);

        // Create new instance of Login class.
        $this->Login = new Login($this->Loader);

        // Template Settings
        $this->twigLoader = new Twig_Loader_Filesystem($_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_VIEW);
        $this->twig = new Twig_Environment($this->twigLoader, array('cache' => $_SERVER['DOCUMENT_ROOT'] . JAMBI_ADMIN_CONTROLLER . 'twig-cache/admin', 'auto_reload' => true, 'autoescape' => false));
        $unserializeFilter = new Twig_SimpleFilter('unserialize', 'unserialize');
        $this->twig->addFilter($unserializeFilter);
        $this->twig->addGlobal('SITE_URL', SITE_URL);
        $this->twig->addGlobal('BASEURL', BASEURL);
        $this->twig->addGlobal('JAMBI_ADMIN', JAMBI_ADMIN);
        $this->twig->addGlobal('JAMBI_ADMIN_CONTENT', JAMBI_ADMIN_CONTENT);
        $this->twig->addGlobal('JAMBI_ADMIN_CONTROLLER', JAMBI_ADMIN_CONTROLLER);
        $this->twig->addGlobal('JAMBI_ADMIN_LIBRARY', JAMBI_ADMIN_LIBRARY);
        $this->twig->addGlobal('JAMBI_ADMIN_SYSTEM', JAMBI_ADMIN_SYSTEM);
        $this->twig->addGlobal('JAMBI_ADMIN_VIEW', JAMBI_ADMIN_VIEW);
        $this->twig->addGlobal('JAMBI_UPLOADS', JAMBI_UPLOADS);
        $this->twig->addGlobal('Jambi', $this);

        // Get Parameters
        $getParameters = array('id', 'module', 'custom', 'action', 'query', 'page', 'pagination', 'redirect');

        foreach ($getParameters as $parameter) {
            $this->get[$parameter] = $this->Loader->Helper->get($parameter);
        }

        $this->get['rec_status'] = $this->Loader->Helper->get('rec_status') != false ? $this->Loader->Helper->get('rec_status') : 0;

        // Module Assignments
        if ($this->get['module'] != false && !empty($this->Loader->Compiler->compiledTables[$this->get['module']])) {
            foreach ($this->Loader->Compiler->compiledTables[$this->get['module']] as $key => $option) {
                $this->table[$key] = $option;
            }
        }

        // Data
        $this->data = $this->getTableRecords();

        // Settings
        $this->settings = $this->getAllSettings();
    }

    /**
     *
     *
     */
    public function getTableRecords(string $table = null)
    {
        $result = array();

        // Table Name
        $table = empty($table) ? $this->get['module'] : $table;

        if (!empty($table)) {
            // Search
            $search = '';

            if ($this->get['query']) {
                $keyword = $this->get['query'];

                foreach ($this->table['fields'] as $key => $value) {
                    $search .= $value["label"] . ' like "%' . $keyword . '%" OR ';
                }

                $search = " AND (" . substr($search, 0, -3) . ")";
            }

            // Pagination
            $result['paginationList'] = array();
            $result['paginationList']['default'] = $this->table['pagination'];
            $statusGroupCounter = $this->Loader->Db->select("SELECT `rec_status`, COUNT(*) as total FROM `$table` WHERE 1 = 1 $search GROUP BY `rec_status`");

            foreach ($statusGroupCounter as $value) {
                $result['count_rec_status'][$value['rec_status']] = $value['total'];

                // Pagination
                $paginationValue = 25;

                while ($value['total'] > $paginationValue) {
                    $paginationValue = $paginationValue * 2;
                    $result['paginationList'][$value['rec_status']][] = $paginationValue;
                }
            }

            $total = array_search($this->get['rec_status'], array_column($statusGroupCounter, 'rec_status'));
            $each = empty($this->get['pagination']) ? $this->table['pagination'] : $this->get['pagination'];
            $page = $this->get['page'];
            $total = $statusGroupCounter[$total]['total'];
            $pages = ceil($total / $each);

            if ($page == '' || $page < 1) {
                $start = 0;
                $currPage = 1;
            } else if ($page > $pages) {
                $start = ($pages - 1) * $each;
                $currPage = $pages;
            } else {
                $start = ($page - 1) * $each;
                $currPage = $page;
            }

            $page = $currPage;
            $getPage = $page;
            $pageLink = $this->Loader->Helper->handleGetUrlParameters(array('add' => array('page')));

            if ($pages > 1) {
                $result['pagination'] .= '<div id="pagination" class="admin-pagination">';
                $result['pagination'] .= '<ul>';

                if ($page != 1) {
                    $result['pagination'] .= '<li class="prev"><a href="' . $pageLink . ($getPage - 1) . '">&lt;</a></li>';
                }

                for ($p = 0; $p < $pages; $p++) {
                    $class = ($getPage == $p + 1) || ($getPage == "" && $p == 0) ? 'class="active"' : null;

                    $result['pagination'] .= '<li ' . $class . '><a href="' . $pageLink . ($p + 1) . '">' . ($p + 1) . '</a></li>';
                }

                if ($page != $pages) {
                    $result['pagination'] .= '<li class="next"><a href="' . $pageLink . ($page + 1) . '">&gt;</a></li>';
                }

                $result['pagination'] .= '</ul>';
                $result['pagination'] .= '</div>';
            }

            $result['sorting']['before'] = $getPage > 1 ? true : false;
            $result['sorting']['after'] = $getPage < $pages ? true : false;

            $sort = $this->table['sort']['field'] . ' ' . $this->table['sort']['direction'];
            $search = '';

            // Select Data
            if ($this->Loader->Compiler->getFieldByType($table, 'permalink') !== false) {
                // Search
                if ($this->get['query']) {
                    $keyword = $this->get['query'];

                    foreach ($this->table['fields'] as $key => $value) {
                        $search .= $table . '.' . $value["label"] . ' like "%' . $keyword . '%" OR ';
                    }

                    $search = "(" . substr($search, 0, -3) . ") AND";
                }

                $result['records'] = $this->Loader->Db->select("SELECT `$table`.*, `system_meta`.full_url FROM `$table` LEFT OUTER JOIN `system_meta` ON `$table`.id = `system_meta`.rec_id AND `system_meta`.rec_table = :rec_table AND `system_meta`.lang = :lang WHERE $search `$table`.rec_status = :rec_status ORDER BY $sort LIMIT $start,$each", array('rec_table' => $table, 'rec_status' => $this->get['rec_status'], 'lang' => $this->Loader->languages['default_lang']));
            } else {
                // Search
                if ($this->get['query']) {
                    $keyword = $this->get['query'];

                    foreach ($this->table['fields'] as $key => $value) {
                        $search .= $value["label"] . ' like "%' . $keyword . '%" OR ';
                    }

                    $search = "(" . substr($search, 0, -3) . ") AND";
                }

                $result['records'] = $this->Loader->Db->select("SELECT * FROM `$table` WHERE $search `rec_status` = :rec_status ORDER BY $sort LIMIT $start,$each", array('rec_status' => $this->get['rec_status']));
            }

            return $result;
        }
    }

    /**
     *
     *
     */
    public function exportTableData(array $data, array $options)
    {
        $fields = $this->Loader->Compiler->get($this->get['module'], 'fields');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $columnNumber = 0;
        $letter = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        foreach ($fields as $index => $field) {
            $fieldsReFactor[$field['label']] = $field;
        }

        foreach ($options['fields'] as $index => $tempDatum) {
            $columnTitle = isset($fieldsReFactor[$tempDatum]['lang']) ? $fieldsReFactor[$tempDatum]['title'] . ' (' . $fieldsReFactor[$tempDatum]['lang'] . ')' : $fieldsReFactor[$tempDatum]['title'];
            $sheet->setCellValue($letter[$columnNumber] . '1', $columnTitle);
            $rowNumber = 2;

            foreach ($data['records'] as $i => $item) {
                if (($fieldsReFactor[$tempDatum]['type'] == 'file') and ($item[$tempDatum] != '')) {
                    $item[$tempDatum] = SITE_URL . JAMBI_UPLOADS . $item[$tempDatum];
                }

                $sheet->setCellValue($letter[$columnNumber] . $rowNumber, $item[$tempDatum]);
                $rowNumber++;
            }

            $sheet->getColumnDimension($letter[$columnNumber])->setWidth(25);
            $columnNumber++;
        }

        if ($options['type'] == 'csv') {
            $file = $this->get['module'] . '_' . date('m_d_y') . '.csv';
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->save($file);
        } elseif ($options['type'] == 'xlxs') {
            $file = $this->get['module'] . '_' . date('m_d_y') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->save($file);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        unlink($file);
        exit();
    }

    /**
     *
     *
     */
    public function getAllSettings()
    {
        $settings = $this->Loader->Db->select("SELECT * FROM `system_settings`");

        if (count($settings) > 0) {
            $result = array();

            foreach ($settings as $value) {
                $lang = $value['lang'] != '' ? $value['lang'] : $this->Loader->languages['first'];
                $result[$value['option_key']][$lang] = stripslashes($value['option_value']);
            }

            return $result;
        } else {
            return false;
        }
    }
}