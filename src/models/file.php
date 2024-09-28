<?php
require_once('config/entity.php');
require_once __DIR__ . '/../services/helper.php';
require_once __DIR__ . '/../controllers/config/file_request.php';

class File extends Entity {
    protected static $table = 'file';

    /**
     * @column
     * primary
     * */
    public int $id;
    /** @column */
    public string $file_name;
    /** @column */
    public ?string $mime;
    /** @column */
    public string $path;
    /** @column */
    public bool $deleted = false;
    /** @column */
    public ?string $name;
    /** @column */
    public int $size = 0;
    /** @column */
    public ?string $description;
    /** @column */
    public datetime $created_at;
    /** @column */
    public ?datetime $deleted_at;

    public function moveToTrash() {
        if (!isset($this->file_name) || $this->deleted) {
            throw new Exception("Error moving to trash", 1);
        }

        $newPath = 'trash';

        Helper::pathExistsCreate(Helper::storagePath($newPath));

        if (rename(Helper::storagePath($this->path . '/' . $this->file_name), Helper::storagePath($newPath . '/' . $this->file_name))) {
            $this->path = $newPath;
            $this->deleted = true;
            $this->deleted_at = new DateTime();
            $this->save();
        } else {
            throw new Exception("Error moving to trash", 1);
        }
    }

    public function carregaArquivo(File_request $file_request, string $path) {
        if (isset($this->file_name) || strlen($path) === 0) throw new Exception("Error receiving the file", 1);

        $match_ext = pathinfo($file_request->name, PATHINFO_EXTENSION);
        if (!$match_ext) throw new Exception("Error with file extention", 1);
        
        $this->file_name = Helper::randomStr(time() . '-') . '.' . (is_array($match_ext) ? $match_ext['extension'] : $match_ext);
        $this->name = $file_request->name;
        $this->size = $file_request->size;
        $this->mime = $file_request->type;
        $this->path = $path;
        
        Helper::pathExistsCreate(Helper::storagePath($this->path));

        $targetFile = Helper::storagePath($this->path . '/' . $this->file_name);

        if (move_uploaded_file($file_request->tmp_name, $targetFile)) {
            $this->save();
        } else {
            throw new Exception("Error with file moving", 1);
        }
    }

    public function url() {
        if (!isset($this->path) || !isset($this->file_name)) throw new Exception("Erro ao obter a rota", 1);
        
        return Helper::uriRoot('storage/' . urlencode($this->path) . '/' . urlencode($this->file_name) . '?name=' . urlencode($this->name));
    }
}