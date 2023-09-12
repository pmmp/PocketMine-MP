<?php

namespace pocketmine\discord;

class Embed {
    public $title = 'OwnagePE Core Embed';
    public $type = 'rich';
    public $description = '';
    public $embedUrl = '';
    public $color = 'FF9A00';
    public $footer = ['text' => '', 'icon_url' => ''];
    public $imageUrl = ['url' => ''];
    public $thumbnailUrl = ['url' => ''];
    public $author = ['name' => '', 'icon_url' => ''];
    public $fields = [];

    public function setTitle (String $title): void{
        $this->title = $title;
    }

    public function setType (String $type): void{
        $this->type = $type;
    }

    public function setDescription (String $description): void{
        $this->description = $description;
    }

    public function setEmbedUrl (String $url): void{
        $this->embedUrl = $url;
    }

    /**HTML COLOR CODES ONLY*/
    public function setColor (String $color): void{
        $this->color = $color;
    }

    public function setFooter (String $text, String $iconUrl = ''): void{
        $this->footer = ['text' => $text, 'icon_url' => $iconUrl];
    }

    public function setImageUrl (String $url): void{
        $this->imageUrl = ['url' => $url];
    }

    public function setThumbnailUrl (String $url): void{
        $this->thumbnailUrl = ['url' => $url];
    }

    public function setAuthor (String $author, String $url = ''): void{
        $this->author = ['name' => $author, 'icon_url' => $url];
    }

    public function addField (String $name, String $value, Bool $inline = false): void{
        $field = [
            'name' => $name,
            'value' => $value,
            'inline' => $inline
        ];
        $this->fields[] = $field;
    }

    public function getEmbed (): array{
        $embed = [
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description,
            'url' => $this->embedUrl,
            'color' => hexdec($this->color),
            'footer' => $this->footer,
            'image' => $this->imageUrl,
            'thumbnail' => $this->thumbnailUrl,
            'author' => $this->author,
            'fields' => $this->fields
        ];

        return $embed;
    }
    
}