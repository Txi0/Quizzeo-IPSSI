<?php
// includes/database.php

class JsonDatabase {
    private $path;

    public function __construct($filename) {
        $this->path = __DIR__ . '/../data/' . $filename;
        if (!file_exists($this->path)) {
            // Créer le dossier data s'il n'existe pas
            if (!file_exists(dirname($this->path))) {
                mkdir(dirname($this->path), 0777, true);
            }
            // Créer le fichier avec un tableau vide
            file_put_contents($this->path, json_encode([]));
        }
    }

    public function getAll() {
        if (!file_exists($this->path)) {
            return [];
        }
        $content = file_get_contents($this->path);
        if (empty($content)) {
            return [];
        }
        return json_decode($content, true) ?? [];
    }

    public function insert($data) {
        $items = $this->getAll();
        $items[] = $data;
        return file_put_contents($this->path, json_encode($items, JSON_PRETTY_PRINT));
    }

    public function update($id, $data) {
        $items = $this->getAll();
        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                $items[$key] = array_merge($item, $data);
                return file_put_contents($this->path, json_encode($items, JSON_PRETTY_PRINT));
            }
        }
        return false;
    }

    public function delete($id) {
        $items = $this->getAll();
        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                unset($items[$key]);
                return file_put_contents($this->path, json_encode(array_values($items), JSON_PRETTY_PRINT));
            }
        }
        return false;
    }

    public function findById($id) {
        $items = $this->getAll();
        foreach ($items as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }
}