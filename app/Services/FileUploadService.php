<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public const TAMANHO_MAXIMO_KB = 5120;

    public function upload($arquivo, $caminho, $extensoesPermitidas = null)
    {
        // extension() deriva da detecção de MIME do conteúdo, não do nome enviado pelo cliente.
        $extensao = strtolower((string) $arquivo->extension());

        if ($extensoesPermitidas && ! in_array($extensao, $extensoesPermitidas, true)) {
            throw new \App\Exceptions\RegraDeNegocioException('Extensão de arquivo não permitida.');
        }

        if ($arquivo->getSize() > self::TAMANHO_MAXIMO_KB * 1024) {
            throw new \App\Exceptions\RegraDeNegocioException('Arquivo muito grande.');
        }

        $caminhoArquivo = $arquivo->store($caminho, 'public');

        return Storage::url($caminhoArquivo);
    }

    public $extensoesImagem = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public $extensoesVideo = ['mp4', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];

    public $extensoesAudio = ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'];

    public $extensoesDocumento = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt', 'ods', 'odp'];

    public $extensoesCompactado = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
}
