<?php

abstract class Storage
{
    abstract public function create(object $object): string;

    abstract public function read(string $slug): ?object;

    abstract public function update(string $slug, object $object): bool;

    abstract public function delete(string $slug): bool;

    abstract public function list(): array;
}

abstract class User
{
    protected int $id;
    protected string $name;
    protected string $role;

    abstract public function getTextsToEdit(): array;
}

class FileStorage extends Storage
{
    private string $storagePath;

    public function __construct(string $storagePath = 'storage')
    {
        $this->storagePath = rtrim($storagePath, '/') . '/';

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }
    }

    /**
     * @param object $object
     * @return string
     */
    public function create(object $object): string
    {
        $baseSlug = $object->slug;
        $date = date('Y-m-d');
        $counter = 0;

        do {
            $filename = $baseSlug . '_' . $date;
            if ($counter > 0) {
                $filename .= '_' . $counter;
            }
            $filename .= '.txt';
            $filePath = $this->storagePath . $filename;

            if (!file_exists($filePath)) {
                break;
            }
            $counter++;
        } while (true);

        $object->slug = $filename;

        $serialized = serialize($object);
        if (file_put_contents($filePath, $serialized) === false) {
            throw new RuntimeException("Failed to write file: $filePath");
        }

        return $object->slug;
    }

    /**
     * @param string $slug
     * @return TelegraphText|null
     */
    public function read(string $slug): ?TelegraphText
    {
        $filePath = $this->storagePath . $slug;

        if (!file_exists($filePath)) {
            $pattern = $this->storagePath . $slug . '*.txt';
            $files = glob($pattern);

            if (empty($files)) {
                return null;
            }
            $filePath = $files[0];
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return null;
        }

        $object = unserialize($fileContent);

        if ($object instanceof TelegraphText) {
            return $object;
        }

        return null;
    }

    /**
     * @param string $slug
     * @param object $object
     * @return bool
     */
    public function update(string $slug, object $object): bool
    {
        $filePath = $this->storagePath . $slug;

        if (!file_exists($filePath)) {
            // Попробуем найти файл
            $pattern = $this->storagePath . $slug . '*.txt';
            $files = glob($pattern);

            if (empty($files)) {
                return false;
            }
            $filePath = $files[0];
        }

        $object->slug = basename($filePath);

        $serialized = serialize($object);
        return file_put_contents($filePath, $serialized) !== false;
    }

    /**
     * @param string $slug
     * @return bool
     */
    public function delete(string $slug): bool
    {
        $filePath = $this->storagePath . $slug;

        if (!file_exists($filePath)) {
            // Попробуем найти файл
            $pattern = $this->storagePath . $slug . '*.txt';
            $files = glob($pattern);

            if (empty($files)) {
                return false;
            }
            $filePath = $files[0];
        }

        return unlink($filePath);
    }

    /**
     * @return array
     */
    public function list(): array
    {
        $texts = [];

        if (!is_dir($this->storagePath)) {
            return $texts;
        }

        $files = scandir($this->storagePath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || pathinfo($file, PATHINFO_EXTENSION) !== 'txt') {
                continue;
            }

            $filePath = $this->storagePath . $file;
            $fileContent = file_get_contents($filePath);

            if ($fileContent === false) {
                continue;
            }

            $object = unserialize($fileContent);

            if ($object instanceof TelegraphText) {
                $texts[] = $object;
            }
        }

        return $texts;
    }
}

class TelegraphText
{
    public string $title;
    public string $text;
    public string $author;
    public string $published;
    public string $slug;

    /**
     * @param string $title
     * @param string $text
     * @param string $author
     */
    public function __construct(string $title, string $text, string $author)
    {
        $this->title = $title;
        $this->text = $text;
        $this->author = $author;
        $this->published = date('Y-m-d');
        $this->slug = $this->generateSlug($title);
    }

    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower(trim($title), 'UTF-8');
        $slug = str_replace(' ', '-', $slug);

        return $slug;
    }

    /**
     * @return string
     */
    public function storeText(): string
    {
        $storage = new FileStorage();
        return $storage->create($this);
    }

    /**
     * @param string $slug
     * @return TelegraphText|null
     */
    public static function loadText(string $slug): ?TelegraphText
    {
        $storage = new FileStorage();
        return $storage->read($slug);
    }

    /**
     * @param string $title
     * @param string $text
     * @return void
     */
    public function editText(string $title, string $text): void
    {
        $this->title = $title;
        $this->text = $text;
        $this->slug = $this->generateSlug($title);
    }
}

$storage = new FileStorage();

$text = new TelegraphText("Programing php", "Изучаем ООП в PHP", "Иван Петров");
$slug = $storage->create($text);

$loadedText = $storage->read($slug);
if ($loadedText) {
    echo "Заголовок: {$loadedText->title}\n";
    echo "Текст: {$loadedText->text}\n";
    echo "Автор: {$loadedText->author}\n";
    echo "Дата: {$loadedText->published}\n\n";
}

$text->editText("Programing php progress level", "Глубокое погружение в ООП");
$storage->update($slug, $text);

$allTexts = $storage->list();
echo "Всего текстов в хранилище: " . count($allTexts) . "\n";
foreach ($allTexts as $i => $text) {
    echo "{$text->title} (автор: {$text->author})\n";
}
