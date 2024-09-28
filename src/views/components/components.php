<?php
// https://getbootstrap.com/docs/5.0/getting-started/introduction/
// https://getbootstrap.com/docs/4.4/components/forms/
// https://getbootstrap.com/docs/4.0/components/buttons/

require_once __DIR__ . '/../../services/helper.php';

class Obj_multiSelect {
    /** @var bool */
    public $checked;
    /** @var string */
    public $name;
    /** @var string */
    public $value;
    /** @var string */
    public $value_to_show;
    /** @var array[] */
    public $inputs = [];
    /** @var string[] */
    public $attributes = [];
    public function __construct(bool $checked, string $name, string $value, string $value_to_show) {
        $this->checked = $checked;
        $this->name = $name;
        $this->value = $value;
        $this->value_to_show = $value_to_show;
    }
    public function addInput(string $name, string $value) {
        $this->inputs[] = [$name, $value];
    }

    public function addAttributes($value) {
        $this->attributes[] = $value;
    }
}

class Components {
    /**
     * @param Obj_multiselect[] $arr
     * @param 'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark'|'link' $btn_class
     */
    static function multiSelect(array $arr, string $default_value = '', string $separator = ', ', string $btn_class = 'success') {
        $index = Helper::randomStr('', 7);
        ?>
            <div class="dropdown d-inline"> 
                <button class="btn btn-<?php echo $btn_class ?> dropdown-toggle text-start"
                        type="button" 
                        id="multiSelectDropdown-<?php echo $index ?>"
                        data-bs-toggle="dropdown" 
                        data-bs-auto-close="outside"
                        aria-expanded="false"> 
                    Status 
                </button> 
                <ul class="dropdown-menu index-multiselect-<?php echo $index ?>"
                    aria-labelledby="multiSelectDropdown"> 
                    <?php
                        foreach ($arr as $item) {
                            ?>
                                <li> 
                                    <label class="text-nowrap">
                                        <?php
                                            foreach ($item->inputs as $input) {
                                                ?>
                                                    <input type="text" hidden name="<?php echo $input[0] ?>" value="<?php echo $input[1] ?>">
                                                <?php
                                            }
                                        ?>
                                        <input type="checkbox" <?php echo implode(' ', $item->attributes) ?> <?php echo $item->checked ? 'checked' : '' ?> name="<?php echo $item->name?>" value="<?php echo $item->value ?>" value_to_show="<?php echo $item->value_to_show ?>">
                                            <?php echo $item->value_to_show ?>
                                    </label>
                                </li> 
                            <?php
                        }
                    ?>
                </ul>
            </div>
            <script>multiSelectEvents("<?php echo $index?>", "<?php echo $default_value?>", "<?php echo $separator?>")</script>
        <?php
    }

    static function dropFileArea(string $name = 'files[]') {
        ?>
            <div class="drop-area" id="drop-area">
                Arraste e solte arquivos aqui ou clique para selecionar
                <input type="file" id="file-upload" name="<?php echo $name ?>" multiple style="display: none;">
            </div>

            <div class="file-list">
                <ul id="file-list"></ul>
            </div>

            <script>
                setDropArea(dropAreaId = 'drop-area', fileListId = 'file-list');
            </script>
        <?php
    }
}