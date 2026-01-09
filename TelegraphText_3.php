<?php

interface IRender
{
    public function render(TelegraphTextThree $telegraphTextThree): string;
}

abstract class View implements IRender
{
    protected string $templateName;
    protected array $variables = [];

    public function __construct(string $templateName)
    {
        $this->templateName = $templateName;
    }

    public function addVariablesToTemplate(array $variables): void
    {
        $this->variables = $variables;
    }
}

class Swig extends View
{
    public function render(TelegraphTextThree $telegraphTextThree): string
    {
        $templatePath = sprintf('templates/%s.swig', $this->templateName);

        $templateContent = file_get_contents($templatePath);

        foreach ($this->variables as $key) {
            $templateContent = str_replace('{{' . $key . '}}', $telegraphTextThree->$key, $templateContent);
        }

        return $templateContent;
    }
}

class Spl extends View
{
    public function render(TelegraphTextThree $telegraphTextThree): string
    {
        $templatePath = sprintf('templates/%s.spl', $this->templateName);

        $templateContent = file_get_contents($templatePath);

        foreach ($this->variables as $key) {
            $templateContent = str_replace('$$' . $key . '$$', $telegraphTextThree->$key, $templateContent);
        }

        return $templateContent;
    }
}

class TelegraphTextThree
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
        $this->published = date('Y-m-d');
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
     * @return TelegraphTextThree|null
     */
    public static function loadText(string $slug): ?TelegraphTextThree
    {
        $fileName = $slug . self::FILE_EXTENSION;
        if (!$fileName) {
            return null;
        }

        $fileContent = file_get_contents($fileName);
        $data = unserialize($fileContent);

        $telegraphText = new TelegraphTextThree($data['title'], $data['text'], $data['author']);
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

$telegraphText = new TelegraphTextThree('Vasya', 'Some slug', 'Alex');
$telegraphText->editText('Some title', 'Some text');

$swig = new Swig('telegraph_text');
$swig->addVariablesToTemplate(['slug', 'text']);

$spl = new Spl('telegraph_text');
$spl->addVariablesToTemplate(['slug', 'title', 'text']);

$templateEngines = [$swig, $spl];
foreach ($templateEngines as $engine) {
    if ($engine instanceof IRender) {
        echo $engine->render($telegraphText) . PHP_EOL;
    } else {
        echo 'Template engine does not support render interface' . PHP_EOL;
    }
}
