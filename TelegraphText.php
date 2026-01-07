<?php

class TelegraphText
{
    public string $title;
    public string $text;
    public string $author;
    public string $published;
    public string $slug;

    private const FILE_EXTENSION = '.txt';

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
        $this->published = date('Y-m-d H:i:s');
        $this->slug = strtolower(str_replace(' ', '-', $title));
    }

    /**
     * Функция создания файла в текстовом формате и добавления в него сериализованного массива
     *
     * @return string
     */
    public function storeText(): string
    {
        $data = [
            'title' => $this->title,
            'text' => $this->text,
            'author' => $this->author,
            'published' => $this->published
        ];

        $serializedData = serialize($data);

        $fileName = $this->slug . self::FILE_EXTENSION;
        file_put_contents($fileName, $serializedData);

        return $this->slug;
    }

    /**
     * Функция извлечения массива из файла и его десиарелизация
     *
     * @param string $slug
     * @return TelegraphText|null
     */
    public static function loadText(string $slug): ?TelegraphText
    {
        $fileName = $slug . self::FILE_EXTENSION;
        if (!$fileName) {
            return null;
        }

        $fileContent = file_get_contents($fileName);
        $data = unserialize($fileContent);

        $telegraphText = new TelegraphText($data['title'], $data['text'], $data['author']);
        $telegraphText->published = $data['published'];

        return $telegraphText;
    }

    /**
     * Функция изменения заголовка и текста
     *
     * @param string $title
     * @param string $text
     * @return void
     */
    public function editText(string $title, string $text): void
    {
        $this->title = $title;
        $this->text = $text;
    }
}

$title = "programing";
$author = "Иван Петров";
$text = "ООП в PHP.";

$telegraph = new TelegraphText($title, $text, $author);

$slug = $telegraph->storeText();

$loadedTelegraph = TelegraphText::loadText($slug);
echo $loadedTelegraph->title . PHP_EOL;
echo $loadedTelegraph->text . PHP_EOL;
echo $loadedTelegraph->author . PHP_EOL;
echo $loadedTelegraph->published . PHP_EOL;
echo '-------------------' . PHP_EOL;

$telegraph->editText('news programing', 'Сегодня мы изучили ООП в PHP.');
$slug = $telegraph->storeText();

$loadedNewTelegraph = TelegraphText::loadText($slug);
echo $loadedNewTelegraph->title . PHP_EOL;
echo $loadedNewTelegraph->text . PHP_EOL;
echo $loadedNewTelegraph->author . PHP_EOL;
echo $loadedNewTelegraph->published . PHP_EOL;
