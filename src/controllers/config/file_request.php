<?php

/**
 * name may include [] or not at the end
 * the name itself may be any one, as long as especified in request class
 *  <input type="file" name="files[]" multiple>
 *  <input type="file" name="file" multiple>
 * 
 * in request class
 *  ?File_request or ?array with notation in comments: File_request[]
 *      ? indicates optional
 *  mimes=jpg,pdf|bpm,...
 *  file_max_size=1mb
 *  file_min_size=100kb
 */

class File_request {
    public ?string $name;
    public ?string $type;
    public ?string $full_path;
    public ?string $tmp_name;
    public ?int $error;
    public ?int $size;

    public function __construct(string $name = null, string $type = null, string $full_path = null, string $tmp_name = null, int $error = null, int $size = null) {
        $this->name = $name;
        $this->type = $type;
        $this->full_path = $full_path;
        $this->tmp_name = $tmp_name;
        $this->error = $error;
        $this->size = $size;
    }
}
