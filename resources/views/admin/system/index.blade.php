@extends('layouts.admin')

@section('title', 'Système')

@section('content')
<div class="p-4 sm:p-6 space-y-5" x-data="systemPanel()">

    {{-- Deploy panel — dark terminal aesthetic intentional --}}
    <div class="bg-gray-900 rounded-xl shadow-sm p-5 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-[15px] font-semibold">Déploiement post-pull</h2>
                <p class="text-gray-400 text-[12px] mt-1">
                    Lance les tâches de mise à jour côté serveur (caches, migrations, OPcache, lien storage).
                    À utiliser après chaque <code class="bg-white/10 px-1.5 py-0.5 rounded">git pull</code> sur le serveur.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <label class="inline-flex items-center gap-2 text-[13px] text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="skipMigrate" class="rounded border-gray-500 bg-gray-800 text-green-500">
                    Sauter migrations
                </label>
                <label class="inline-flex items-center gap-2 text-[13px] text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="skipCache" class="rounded border-gray-500 bg-gray-800 text-green-500">
                    Sauter re-cache
                </label>
                <button type="button"
                        @click="runDeploy()"
                        :disabled="running"
                        class="h-9 px-4 bg-green-600 hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold text-[13px] rounded-lg transition-colors flex items-center gap-2">
                    <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg x-show="running" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="running ? 'Déploiement…' : 'Lancer le déploiement'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Console output --}}
    <div x-show="output" x-cloak x-transition class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                <span class="text-[11px] text-gray-400 ml-2">Sortie de la commande</span>
            </div>
            <div class="flex items-center gap-2">
                <span x-show="lastExit === 0" class="text-[11px] px-2 py-0.5 rounded-full bg-green-500/20 text-green-300">Succès</span>
                <span x-show="lastExit !== null && lastExit !== 0" class="text-[11px] px-2 py-0.5 rounded-full bg-red-500/20 text-red-300">Échec (code: <span x-text="lastExit"></span>)</span>
                <button type="button" @click="output = ''" class="text-[11px] text-gray-400 hover:text-white">Fermer</button>
            </div>
        </div>
        <pre class="p-4 text-[11px] text-green-300 font-mono overflow-x-auto whitespace-pre-wrap" x-text="output" style="max-height: 400px; overflow-y: auto;"></pre>
    </div>

    {{-- Info cards --}}
    <div class="grid lg:grid-cols-2 gap-5">

        {{-- Application --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-semibold text-gray-900">Application</h3>
                @php $env = $systemInfo['app']['env']; @endphp
                <span class="text-[11px] px-2.5 py-1 rounded-full {{ $env === 'production' ? 'bg-green-100 text-green-700' : ($env === 'local' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                    {{ $env }}
                </span>
            </div>
            <dl class="space-y-2.5 text-[13px]">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">URL</dt>
                    <dd class="text-gray-900 font-medium truncate">{{ $systemInfo['app']['url'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Mode debug</dt>
                    <dd>
                        @if($systemInfo['app']['debug'])
                            <span class="text-red-600 font-semibold">Activé (à désactiver en prod)</span>
                        @else
                            <span class="text-green-600 font-medium">Désactivé</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Locale</dt>
                    <dd class="text-gray-900 font-medium">{{ $systemInfo['app']['locale'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Fuseau horaire</dt>
                    <dd class="text-gray-900 font-medium">{{ $systemInfo['app']['timezone'] }}</dd>
                </div>
            </dl>
        </div>

        {{-- Git --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-semibold text-gray-900">Version déployée (Git)</h3>
                @if($systemInfo['git']['head'])
                    <span class="text-[11px] px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 font-mono">
                        {{ $systemInfo['git']['branch'] }}@{{ $systemInfo['git']['head'] }}
                    </span>
                @endif
            </div>
            @if($systemInfo['git']['head'])
                <dl class="space-y-2.5 text-[13px]">
                    <div class="border-b border-gray-100 pb-2">
                        <dt class="text-gray-500 mb-1">Dernier commit</dt>
                        <dd class="text-gray-900">{{ $systemInfo['git']['commit_message'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date</dt>
                        <dd class="text-gray-900 font-medium">{{ $systemInfo['git']['commit_date'] ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-[13px] text-gray-500">Aucune information Git disponible (le dossier <code>.git</code> n'est pas présent).</p>
            @endif
        </div>

        {{-- PHP & extensions --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">PHP & extensions</h3>
            <dl class="space-y-2.5 text-[13px]">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Version PHP</dt>
                    <dd class="text-gray-900 font-mono font-medium">{{ $systemInfo['php']['version'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">memory_limit</dt>
                    <dd class="text-gray-900 font-mono">{{ $systemInfo['php']['memory_limit'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">upload_max_filesize</dt>
                    <dd class="text-gray-900 font-mono">{{ $systemInfo['php']['upload_max_filesize'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">post_max_size</dt>
                    <dd class="text-gray-900 font-mono">{{ $systemInfo['php']['post_max_size'] }}</dd>
                </div>
                <div class="pt-1">
                    <dt class="text-gray-400 text-[11px] uppercase tracking-wider mb-2">Extensions critiques</dt>
                    <dd class="flex flex-wrap gap-1.5">
                        @foreach($systemInfo['php']['extensions'] as $name => $loaded)
                            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $loaded ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $name }}{{ $loaded ? '' : ' (manquant)' }}
                            </span>
                        @endforeach
                    </dd>
                </div>
            </dl>
        </div>

        {{-- OPcache --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-semibold text-gray-900">OPcache</h3>
                @if($systemInfo['opcache']['enabled'])
                    <span class="text-[11px] px-2.5 py-1 rounded-full bg-green-100 text-green-700">Actif</span>
                @else
                    <span class="text-[11px] px-2.5 py-1 rounded-full bg-red-100 text-red-700">Inactif</span>
                @endif
            </div>
            @if($systemInfo['opcache']['enabled'])
                <dl class="space-y-2.5 text-[13px]">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Scripts en cache</dt>
                        <dd class="text-gray-900 font-medium">{{ number_format($systemInfo['opcache']['cached_scripts'] ?? 0) }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Mémoire utilisée</dt>
                        <dd class="text-gray-900 font-medium">{{ $systemInfo['opcache']['memory_used'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Mémoire libre</dt>
                        <dd class="text-gray-900 font-medium">{{ $systemInfo['opcache']['memory_free'] ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <div class="rounded-lg bg-amber-50 border border-amber-100 p-4 text-[13px] text-amber-800">
                    <p class="font-semibold mb-1">OPcache désactivé</p>
                    <p>Les performances en production seront fortement dégradées. Demandez à votre hébergeur d'activer l'extension <code>opcache</code>.</p>
                </div>
            @endif
        </div>

        {{-- Caches Laravel --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Caches Laravel <span class="text-[11px] font-normal text-gray-400">(bootstrap/cache/)</span></h3>
            <dl class="space-y-1.5 text-[13px]">
                @foreach($systemInfo['caches'] as $name => $info)
                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100 last:border-0">
                        <dt class="text-gray-500 font-mono text-[11px]">{{ $name }}.php</dt>
                        <dd>
                            @if($info['present'])
                                <span class="text-[11px] text-gray-500">{{ $info['mtime'] }}</span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-green-100 text-green-700 ml-2">actif</span>
                            @else
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">absent</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            <p class="text-[11px] text-gray-400 mt-3">Ces fichiers sont régénérés par <code>deploy:after-pull</code>.</p>
        </div>

        {{-- Stockage --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <h3 class="text-[14px] font-semibold text-gray-900 mb-4">Stockage</h3>
            <dl class="space-y-2.5 text-[13px]">
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Lien public/storage</dt>
                    <dd>
                        @if($systemInfo['storage']['public_link_exists'])
                            <span class="text-green-600 font-medium">OK</span>
                        @else
                            <span class="text-red-600 font-semibold">Manquant</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">storage/app/public</dt>
                    <dd>
                        @if($systemInfo['storage']['storage_path_exists'])
                            <span class="text-green-600 font-medium">OK</span>
                        @else
                            <span class="text-red-600 font-semibold">Manquant</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Storage inscriptible</dt>
                    <dd>
                        @if($systemInfo['storage']['storage_writable'])
                            <span class="text-green-600 font-medium">Oui</span>
                        @else
                            <span class="text-red-600 font-semibold">Non (chmod -R 775 storage)</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Logs inscriptibles</dt>
                    <dd>
                        @if($systemInfo['storage']['logs_writable'])
                            <span class="text-green-600 font-medium">Oui</span>
                        @else
                            <span class="text-red-600 font-semibold">Non</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="text-[11px] text-gray-400 text-right">État relevé à {{ $systemInfo['now'] }}</div>
</div>

<script>
function systemPanel() {
    return {
        running: false,
        skipMigrate: false,
        skipCache: false,
        output: '',
        lastExit: null,
        async runDeploy() {
            if (this.running) return;
            if (!confirm('Lancer la commande de déploiement ?\n\nCela va vider et regénérer les caches, et exécuter les migrations.')) return;

            this.running = true;
            this.output = 'Démarrage…\n';
            this.lastExit = null;

            try {
                const r = await fetch('{{ route('admin.system.deploy') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        skip_migrate: this.skipMigrate,
                        skip_cache: this.skipCache,
                    })
                });
                const d = await r.json();
                this.output = d.output || '(aucune sortie)';
                this.lastExit = d.exit_code ?? (d.success ? 0 : 1);
            } catch (e) {
                this.output = 'Erreur réseau : ' + e.message;
                this.lastExit = 1;
            } finally {
                this.running = false;
            }
        }
    }
}
</script>
@endsection
