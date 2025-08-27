<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\BackgroundRemovalService;
use App\Services\ChatGPTService;
use App\Services\ImageGenerationService;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
class ProductController extends Controller
{
    protected BackgroundRemovalService $bgRemoval;
    protected ChatGPTService $chatGPT;
    protected ImageGenerationService $imageGen;

    public function __construct(
        BackgroundRemovalService $bgRemoval,
        ChatGPTService $chatGPT,
        ImageGenerationService $imageGen
    ) {
        $this->bgRemoval = $bgRemoval;
        $this->chatGPT = $chatGPT;
        $this->imageGen = $imageGen;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
            'description' => 'required|string|max:255',
        ]);

        $user = User::first();

        // Save original photo
        $path = $request->file('photo')->store('originals');

        // Remove background
        $bgRemovedPath = $this->bgRemoval->removeBackground($path);

        // Save photo info to DB
        $photo = new Photo();
        $photo->user_id = $user->id;
        $photo->original_path = $path;
        $photo->bg_removed_path = $bgRemovedPath;
        $photo->save();

        // Generate templates using ChatGPT
        $templates = $this->chatGPT->generateTemplates($request->input('description'));

        // Generate images from templates
        $generatedImages = $this->imageGen->generateProductCards($bgRemovedPath, $templates);

        // Return all data to frontend
        return response()->json([
            'photo_id' => $photo->id,
            'templates' => $templates,
            'generated_images' => array_map(fn($p) => Storage::url($p), $generatedImages),
        ]);
    }
}
