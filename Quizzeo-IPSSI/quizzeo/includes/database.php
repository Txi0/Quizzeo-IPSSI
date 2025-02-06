<?php
// includes/database.php

class JsonDatabase {
    private $path;

    public function __construct($filename) {
        $this->path = __DIR__ . '/../data/' . $filename;
        
        if (!file_exists($this->path)) {
            if (!is_dir(dirname($this->path))) {
                mkdir(dirname($this->path), 0777, true);
            }
            file_put_contents($this->path, json_encode([]));
        }
    }

    public function getAll() {
        $content = @file_get_contents($this->path);
        if ($content === false || empty($content)) {
            return [];
        }
        return json_decode($content, true) ?? [];
    }

    public function insert($data) {
        $items = $this->getAll();
        $items[] = $data;
        return $this->save($items);
    }

    public function update($id, $data) {
        $items = $this->getAll();
        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                $items[$key] = array_merge($item, $data);
                return $this->save($items);
            }
        }
        return false;
    }

    public function delete($id) {
        $items = $this->getAll();
        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                unset($items[$key]);
                return $this->save(array_values($items));
            }
        }
        return false;
    }

    public function findById($id) {
        foreach ($this->getAll() as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    // Filtrer par un champ spécifique (exemple: 'user_id')
    public function findByField($field, $value) {
        return array_filter($this->getAll(), function($item) use ($field, $value) {
            return isset($item[$field]) && $item[$field] == $value;
        });
    }

    // Fonction privée pour sauvegarder les données
    private function save($items) {
        return file_put_contents($this->path, json_encode($items, JSON_PRETTY_PRINT));
    }
}
