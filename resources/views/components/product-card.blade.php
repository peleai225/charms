@props([
    'product' => null,
    'image' => null,
    'title' => '',
    'price' => 0,
    'oldPrice' => null,
    'category' => null,
    'rating' => null,
    'reviews' => null,
    'badge' => null,
    'badgeVariant' => 'danger',
    'href' => '#',
    'inStock' => true,
])

<div {{ $attributes->merge(['class' => 'group bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-1']) }}>
    <!-- Image -->
    <a href="{{ $href }}" class="block relative aspect-square bg-slate-100 overflow-hidden">
        @if($image)
            <img 
                src="{{ $image }}" 
                alt="{{ $title }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
        
        @if($badge)
            <span class="absolute top-3 left-3 px-2.5 py-1 text-xs font-semibold rounded-full
                @if($badgeVariant === 'danger') bg-danger-500 text-white
                @elseif($badgeVariant === 'success') bg-success-500 text-white
                @elseif($badgeVariant === 'warning') bg-warning-500 text-white
                @else bg-primary-500 text-white
                @endif
            ">
                {{ $badge }}
            </span>
        @endif
        
        <!-- Quick actions -->
        <div class="absolute inset-0 bg-slate-900/0 group-hover:bg-slate-900/20 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
            <button class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-700 hover:bg-primary-600 hover:text-white transition-all shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
            <button class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-slate-700 hover:bg-primary-600 hover:text-white transition-all shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300 delay-75">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
            <button class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white hover:bg-primary-700 transition-all shadow-lg transform translate-y-4 group-hover:translate-y-0 duration-300 delay-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </button>
        </div>
    </a>
    
    <!-- Content -->
    <div class="p-4">
        @if($category)
            <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ $category }}</p>
        @endif
        
        <a href="{{ $href }}" class="block">
            <h3 class="font-semibold text-slate-900 line-clamp-2 hover:text-primary-600 transition-colors">
                {{ $title }}
            </h3>
        </a>
        
        @if($rating !== null)
            <div class="flex items-center gap-1.5 mt-2">
                <div class="flex items-center">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $rating ? 'text-accent-400' : 'text-slate-200' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                @if($reviews !== null)
                    <span class="text-xs text-slate-500">({{ $reviews }})</span>
                @endif
            </div>
        @endif
        
        <div class="flex items-center justify-between mt-3">
            <div class="flex items-baseline gap-2">
                <span class="text-lg font-bold text-primary-600">{{ format_price($price) }}</span>
                @if($oldPrice)
                    <span class="text-sm text-slate-400 line-through">{{ format_price($oldPrice) }}</span>
                @endif
            </div>
            
            @if(!$inStock)
                <span class="text-xs font-medium text-danger-600">Rupture</span>
            @endif
        </div>
    </div>
</div>

