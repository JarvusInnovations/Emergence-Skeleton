<?php

class PDFMedia extends Media
{
    // configurables
    public static $extractPageCommand = 'convert \'%1$s[%2$u]\' JPEG:- 2>/dev/null'; // 1=pdf path, 2=page
    public static $extractPageIndex = 0;

    public static $mimeHandlers = [
        'application/pdf',
        'application/postscript',
        'image/svg+xml'
    ];

    public function getValue($name)
    {
        switch ($name) {
            case 'ThumbnailMIMEType':
                return 'image/png';

            case 'Extension':

                switch ($this->MIMEType) {
                    case 'application/pdf':
                        return 'pdf';
                    case 'application/postscript':
                        return 'eps';
                    case 'image/svg+xml':
                        return 'svg';
                    default:
                        throw new Exception('Unable to find document extension for mime-type: '.$this->MIMEType);
                }

            default:
                return parent::getValue($name);
        }
    }

    // public methods
    public function getImage(array $options = [])
    {
        foreach ([$this->FilesystemPath, $this->BlankPath] as $sourceFile) {
            $cmd = sprintf(static::$extractPageCommand, $sourceFile, static::$extractPageIndex);

            if ($imageData = shell_exec($cmd)) {
                return imagecreatefromstring($imageData);
            }
        }

        return null;
    }

    // static methods
    public static function analyzeFile($filename, $mediaInfo = [])
    {
        $mediaInfo = parent::analyzeFile($filename, $mediaInfo);

        $cmd = sprintf(static::$extractPageCommand, $filename, static::$extractPageIndex);
        $pageIm = @imagecreatefromstring(shell_exec($cmd));

        if (!$pageIm) {
            throw new MediaTypeException('Unable to convert PDF, ensure that imagemagick is installed on the server');
        }

        $mediaInfo['width'] = imagesx($pageIm);
        $mediaInfo['height'] = imagesy($pageIm);

        return $mediaInfo;
    }
}