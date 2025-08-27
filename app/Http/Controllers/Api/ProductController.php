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
use App\Services\ProductCardGenerationService;
class ProductController extends Controller
{
    protected BackgroundRemovalService $bgRemoval;
    protected ChatGPTService $chatGPT;
    protected ImageGenerationService $imageGen;
    protected ProductCardGenerationService $cardGen;

    public function __construct(
        BackgroundRemovalService $bgRemoval,
        ChatGPTService $chatGPT,
        ImageGenerationService $imageGen,
        ProductCardGenerationService $cardGen
    ) {
        $this->bgRemoval = $bgRemoval;
        $this->chatGPT = $chatGPT;
        $this->imageGen = $imageGen;
        $this->cardGen = $cardGen;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|max:5120',
            'product_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:120',
            'category' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:1024',
        ]);

        $user = User::first();

        // Save original photo on public disk
        $path = $request->file('photo')->store('originals', 'public');

        // Remove background
        $bgRemovedPath = $this->bgRemoval->removeBackground($path);

        // Save photo info to DB
        $photo = new Photo();
        $photo->user_id = $user->id;
        $photo->original_path = $path;
        $photo->bg_removed_path = $bgRemovedPath;
        $photo->save();

        // Generate templates using ChatGPT (fallback if no description provided)
        $templates = $this->chatGPT->generateTemplates(
            $request->input('description') ?: $request->input('product_name')
        );

        // Generate images from templates via DALL·E
        $generatedImages = $this->imageGen->generateProductCards($bgRemovedPath, $templates);

        // Generate marketplace product card data
        $productCard = $this->cardGen->generate([
            'name' => $request->input('product_name'),
            'brand' => $request->input('brand'),
            'category' => $request->input('category'),
            'price' => $request->input('price'),
            'sku' => $request->input('sku'),
            'description' => $request->input('description'),
        ]);

        // Persist generated images and product card on photo
        $photo->generated_images = $generatedImages;
        $photo->product_card = $productCard;
        $photo->save();

        // Return all data to frontend
        return response()->json([
            'photo_id' => $photo->id,
            'original_url' => Storage::disk('public')->url($photo->original_path),
            'bg_removed_url' => $photo->bg_removed_path ? Storage::disk('public')->url($photo->bg_removed_path) : null,
            'templates' => $templates,
            'generated_images' => array_map(fn($p) => Storage::disk('public')->url($p), $generatedImages),
            'product_card' => $productCard,
        ]);
    }
}
