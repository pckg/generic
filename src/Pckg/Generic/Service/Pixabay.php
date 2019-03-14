<?php namespace Pckg\Generic\Service;

class Pixabay
{

    public function getPictures($search)
    {
        $pictures = context()->getOrDefault(Pixabay::class . ':' . $search, []);

        if ($pictures) {
            return $pictures;
        }

        $this->fetchPictures($search);

        return $pictures;
    }

    public function getPicture($folder, $search)
    {
        $pictures = $this->fetchPictures($search);
        $picture = $pictures[array_rand($pictures)];

        if ($folder) {
            $this->copyPicture($picture, $folder);
        }

        return $picture;
    }

    public function copyPicture($picture, $folder)
    {
        $fullPath = path('uploads') . $picture;
        $finalPath = path('uploads') . $folder . path('ds') . $picture;
        if (file_exists($finalPath)) {
            return;
        }
        if (!is_dir(path('uploads') . $folder)) {
            mkdir(path('uploads') . $folder);
        }
        file_put_contents($finalPath, file_get_contents($fullPath));
    }

    public function fetchPictures($search)
    {
        $client = new Client();
        $key = config('pckg.generic.external.pixabax.key', '');
        $url = 'https://pixabay.com/api/?key=' . $key . '&q=' . urlencode($search) . '&min_width=1600' .
            '&min_height=900' . '&safe_search=true' . '&editors_choice=true' . '&orientation=horizontal' .
            '&image_type=photo';
        $pictures = json_decode($client->get($url)->getBody()->getContents())->hits ?? [];
        $pictures = collect($pictures)->map(function($picture) {
            $path = sha1($picture->id) . '.' .
                substr($picture->largeImageURL, 1 + strrpos($picture->largeImageURL, '.'));
            $fullPath = path('uploads') . $path;
            if (!file_exists($fullPath)) {
                file_put_contents($fullPath, file_get_contents($picture->largeImageURL));
            }

            return $path;
        })->all();

        /**
         * Save pictures to Context for reuse?
         */
        context()->bind(Pixabay::class . ':' . $search, $pictures);

        return $pictures;
    }

}