<?php
require_once __DIR__ . '/../../services/log.php';

class Page {
    /** @var int */
    public $page;
    /** @var int */
    public $per_page;
    /** @var int */
    public $rows_count;
    /** @var int */
    public $total_pages;

    public function __construct(int $page, int $per_page, int $rows_count) {
        $this->page = $page;
        $this->per_page = $per_page;
        $this->rows_count = $rows_count;
        $this->total_pages = ceil($rows_count / $per_page);
    }

    public function hiddenInputs(bool $skip_per_page = false) {
        ?>
            <input type="text" hidden name="page" id="" value="<?php echo $this->page ?>">
        <?php
        if (!$skip_per_page) {
            ?>
                <input type="text" hidden name="per_page" id="" value="<?php echo $this->per_page ?>">
            <?php
        }
    }

    public function linkPage($page_number, string $query_string = '') {
        $end = empty($query_string) ? '' : '&' . $query_string;
        return '?per_page=' . $this->per_page . '&page=' . $page_number . $end;
    }

    public function htmlPagesNumbers(string $query_string = '', int $pages_visible = 15) {
        // https://getbootstrap.com/docs/4.0/components/pagination/
        ?>
            <div aria-label="Page navigation example">
                <ul class="pagination">
                    <?php
                        echo "<li class=\"page-item\"><a add-hash class=\"page-link\" href=\"" . $this->linkPage(1, $query_string) . "\">Primeira</a></li>";
                        $pDisabled = $this->page > 1 ? '' : ' disabled';
                        echo "<li class=\"page-item{$pDisabled}\"><a add-hash {$pDisabled} class=\"page-link\" href=\"" . $this->linkPage($this->page - 1, $query_string) . "\">Anterior</a></li>";

                        $half_pages_visible = (int) floor($pages_visible / 2);
                        $start_page = (int) max($this->page - $half_pages_visible, 1);
                        $end_page = (int) min($start_page + $pages_visible - 1, $this->total_pages);
                        $start_page = (int) max($end_page - $pages_visible + 1, 1);

                        if ($start_page > 1) echo "<li class=\"page-item disabled\"><a add-hash disabled class=\"page-link\" href=\"\">...</a></li>";
                        for ($i=$start_page; $i <= $end_page ; $i++) { 
                            $selected = $i === $this->page ? ' active' : '';
                            echo "<li class=\"page-item{$selected}\"><a add-hash class=\"page-link\" href=\"{$this->linkPage($i, $query_string)}\">{$i}</a></li>";
                        }

                        if ($end_page < $this->total_pages) echo "<li class=\"page-item disabled\"><a add-hash disabled class=\"page-link\" href=\"\">...</a></li>";

                        $nDisabled = $this->page < $this->total_pages ? '' : ' disabled';
                        echo "<li class=\"page-item{$nDisabled}\"><a add-hash {$nDisabled} class=\"page-link\" href=\"" . $this->linkPage($this->page + 1, $query_string) . "\">Próximo</a></li>";
                        
                        echo "<li class=\"page-item\"><a add-hash class=\"page-link\" href=\"" . $this->linkPage($this->total_pages, $query_string) . "\">Última</a></li>";
                    ?>
                </ul>
            </div>
        <?php
    }
}