<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\Project;
use App\Support\PrivateStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProjectDocumentService
{
    public function store(Project $project, UploadedFile $file, ?string $label = null): MediaFile
    {
        $filename = $file->getClientOriginalName();
        $path = $file->storeAs(
            'projects/'.$project->id.'/attachments',
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'private',
        );

        return MediaFile::create([
            'project_id' => $project->id,
            'filename' => $filename,
            'label' => $label ?: pathinfo($filename, PATHINFO_FILENAME),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'source_kind' => $file->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'document',
            'size_bytes' => $file->getSize(),
        ]);
    }

    public function delete(MediaFile $media): void
    {
        if ($media->path) {
            PrivateStorage::delete($media->path);
        }

        $media->delete();
    }
}
