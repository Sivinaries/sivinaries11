<!DOCTYPE html>
<html lang="en">

<head>
    <title>Home</title>
    @include('user.layout.head')
    <style>
        .slider-container {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .slider-wrapper {
            display: flex;
            transition: transform 0.5s ease-in-out;
            gap: 20px;
        }

        .slider-slide {
            min-width: 100%;
            box-sizing: border-box;
        }

        .slider-slide img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        /* Automatic sliding effect using keyframes */
        @keyframes slideAnimation {
            0% {
                transform: translateX(0);
            }

            33% {
                transform: translateX(-100%);
            }

            66% {
                transform: translateX(-200%);
            }

            100% {
                transform: translateX(0);
            }
        }

        .slider-wrapper {
            animation: slideAnimation 10s infinite;
        }
    </style>
</head>

<body class="font-poppins bg-gray-50">
    <div class='w-full sm:max-w-sm mx-auto '>
        <div class='sm:max-w-sm'>
            <div class="space-y-2">
                <div class="bg-white p-2 shadow-xl rounded-b-[20px]">
                    <div class="space-y-2">
                        <div class="my-auto">
                            <img class="mx-auto w-20 h-12" src="{{ asset('/img/beil.svg') }}" alt="">
                        </div>
                        <div class="my-auto">
                            <h1 class="text-2xl text-black font-bold">Stores</h1>
                        </div>
                        <div class="flex justify-between">
                            <div class="space-y-2">
                                <div>
                                    <h1 class="text-2xl font-base text-black">
                                        Welcome!
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 space-y-6">
                    <div class="">
                        @foreach ($stores as $item)
                            <a href="{{ route('user-home') }}">
                                <div class="bg-white p-2 rounded-md border border-red-800 space-y-1">
                                    <div>
                                        <h1 class="text-black text-sm font-bold">{{ $item->store }}</h1>
                                    </div>
                                    <hr>
                                    <div>
                                        <h1 class="text-black text-sm font-bold">{{ $item->address }}</h1>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
