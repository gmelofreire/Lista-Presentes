<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function upload($arquivo, $caminho, $extensoesPermitidas = null)
    {
        $extensao = $arquivo->extension();
        
        if ($extensoesPermitidas) {
            if (!in_array($extensao, $extensoesPermitidas)) {
                throw new \Exception('Extensão de arquivo não permitida.');
            }
        }
        
        $caminhoArquivo = $arquivo->store($caminho, 'public');

        return Storage::url($caminhoArquivo);
    }

    public $extensoesImagem = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg', 'ico', 'heic'];

    public $extensoesVideo = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];

    public $extensoesAudio = ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'];

    public $extensoesDocumento = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt', 'ods', 'odp'];

    public $extensoesCompactado = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];

}


