<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class UploadController extends Controller
{
    /**
     * Diretório da biblioteca de imagens.
     *
     * Configurável por UPLOADS_BASE_PATH. Quando não configurado, mantém o
     * caminho legado do servidor (se existir) ou usa storage/app/uploads.
     */
    private function pastaBase(): string
    {
        $configurado = trim((string) config('filesystems.uploads.base_path'));

        if ($configurado !== '') {
            return rtrim($configurado, '/\\');
        }

        $legado = '/home/ftpuser/ftp/upload';

        if (File::isDirectory($legado)) {
            return $legado;
        }

        return storage_path('app/uploads');
    }

    private function pastasDisponiveis(): array
    {
        $base = $this->pastaBase();

        if (! File::isDirectory($base)) {
            return [];
        }

        return collect(File::directories($base))
            ->mapWithKeys(fn ($diretorio) => [
                basename($diretorio) => basename($diretorio),
            ])
            ->sortKeys()
            ->all();
    }

    public function imagem(string $pasta, string $nome)
    {
        abort_unless(array_key_exists($pasta, $this->pastasDisponiveis()), 404);
        abort_unless(
            $nome === basename($nome) &&
            preg_match('/\.(jpe?g|png|gif|webp)$/i', $nome),
            404
        );

        $arquivo = $this->pastaBase().DIRECTORY_SEPARATOR.$pasta.DIRECTORY_SEPARATOR.$nome;

        abort_unless(File::isFile($arquivo), 404);

        return response()->file($arquivo);
    }

    public function index(Request $request)
    {
        $pastas = $this->pastasDisponiveis();
        $pastaSelecionada = $request->query('pasta');
        $imagensSalvas = [];

        if ($pastaSelecionada && array_key_exists($pastaSelecionada, $pastas)) {
            $diretorio = $this->pastaBase().DIRECTORY_SEPARATOR.$pastaSelecionada;

            $imagensSalvas = collect(File::files($diretorio))
                ->filter(fn ($arquivo) => in_array(
                    strtolower($arquivo->getExtension()),
                    ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    true
                ))
                ->map(fn ($arquivo) => $arquivo->getFilename())
                ->values()
                ->all();
        }

        return view('uploads.index', compact('pastas', 'pastaSelecionada', 'imagensSalvas'));
    }

    public function store(Request $request)
    {
        $pastas = $this->pastasDisponiveis();

        $request->validate([
            'pasta_destino' => ['required', 'string', Rule::in(array_keys($pastas))],
            'imagens' => ['required', 'array', 'min:1'],
            'imagens.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $pasta = $request->input('pasta_destino');
        $destino = $this->pastaBase().DIRECTORY_SEPARATOR.$pasta;
        $imagens = $request->file('imagens');

        // Verifica nomes repetidos no próprio envio e arquivos já existentes.
        $nomes = [];

        foreach ($imagens as $imagem) {
            // basename() já é aplicado pelo Symfony; reforçamos aqui e
            // recusamos nomes vazios ou ocultos.
            $nome = basename($imagem->getClientOriginalName());

            if ($nome === '' || str_starts_with($nome, '.')) {
                return back()
                    ->withErrors(['imagens' => 'Um dos arquivos possui um nome inválido.'])
                    ->withInput();
            }

            $chave = mb_strtolower($nome);

            if (isset($nomes[$chave]) || file_exists($destino.DIRECTORY_SEPARATOR.$nome)) {
                return back()
                    ->withErrors(['imagens' => "O arquivo '{$nome}' já existe ou foi selecionado mais de uma vez."])
                    ->withInput();
            }

            $nomes[$chave] = true;
        }

        foreach ($imagens as $imagem) {
            $imagem->move($destino, basename($imagem->getClientOriginalName()));
        }

        return redirect()
            ->route('uploads.index', ['pasta' => $pasta])
            ->with('success', count($imagens)." imagem(ns) enviada(s) para {$pasta}.");
    }
}
