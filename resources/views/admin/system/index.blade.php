@extends('layouts.admin')

@section('title', 'Système')
@section('page-title', 'Système & Déploiement')

@section('content')
<div class="space-y-6" x-data="systemPanel()">
    {{-- En-tête déploiement --}}
    <div class="bg-gradient-to-r from-slate-900 to-slate-700 rounded-2xl shadow-sm p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">Déploiement post-pull</h2>
                <p class="text-slate-300 text-sm mt-1">
                    Lance les tâches de mise à jour côté serveur (caches, migrations, OPcache, lien storage).
                    À utiliser après chaque <code class="bg-white/10 px-1.5 py-0.5 rounded">git pull</code> sur le serveur.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" x-model="skipMigrate" class="rounded border-slate-500 bg-slate-800 text-indigo-500">
                    Sauter migrations
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" x-model="skipCache" class="rounded border-slate-500 bg-slate-800 text-indigo-500">
                    Sauter re-cache
                </label>
                <button type="button"
                        @click="runDeploy()"
                        :disabled="running"
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold rounded-xl shadow-md transition-all flex items-center gap-2">
                    <svg x-show="!running" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
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

    {{-- Output console (visible après lancement) --}}
    <div x-show="output" x-cloak x-transition class="bg-slate-900 rounded-2xl shadow-sm border border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-2 bg-slate-800 border-b border-slate-700">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                <span class="text-xs text-slate-400 ml-2">Sortie de la commande</span>
            </div>
            <div class="flex items-center gap-2">
                <span x-show="lastExit === 0" class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300">Succès</span>
                <span x-show="lastExit !== null && lastExit !== 0" class="text-xs px-2 py-0.5 rounded-full bg-red-500/20 text-red-300">Échec (code: <span x-text="lastExit"></span>)</span>
                <button type="button" @click="output = ''" class="text-xs text-slate-400 hover:text-white">Fermer</button>
            </div>
        </div>
        <pre class="p-4 text-xs text-emerald-300 font-mono overflow-x-auto whitespace-pre-wrap" x-text="output" style="max-height: 400px; overflow-y: auto;"></pre>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Application --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Application</h3>
                @php $env = $systemInfo['app']['env']; @endphp
                <span class="text-xs px-2.5 py-1 rounded-full {{ $env === 'production' ? 'bg-emerald-100 text-emerald-700' : ($env === 'local' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                    {{ $env }}
                </span>
            </div>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">URL</dt>
                    <dd class="text-slate-900 font-medium truncate">{{ $systemInfo['app']['url'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Mode debug</dt>
                    <dd>
                        @if($systemInfo['app']['debug'])
                            <span class="text-red-600 font-semibold">Activé (à désactiver en prod)</span>
                        @else
                            <span class="text-emerald-600 font-medium">Désactivé</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Locale</dt>
                    <dd class="text-slate-900 font-medium">{{ $systemInfo['app']['locale'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Fuseau horaire</dt>
                    <dd class="text-slate-900 font-medium">{{ $systemInfo['app']['timezone'] }}</dd>
                </div>
            </dl>
        </div>

        {{-- Git --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">Version déployée (Git)</h3>
                @if($systemInfo['git']['head'])
                    <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 font-mono">
                        {{ $systemInfo['git']['branch'] }}@{{ $systemInfo['git']['head'] }}
                    </span>
                @endif
            </div>
            @if($systemInfo['git']['head'])
                <dl class="space-y-2.5 text-sm">
                    <div class="border-b border-slate-100 pb-2">
                        <dt class="text-slate-500 mb-1">Dernier commit</dt>
                        <dd class="text-slate-900">{{ $systemInfo['git']['commit_message'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Date</dt>
                        <dd class="text-slate-900 font-medium">{{ $systemInfo['git']['commit_date'] ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-500">Aucune information Git disponible (le dossier <code>.git</code> n'est pas présent).</p>
            @endif
        </div>

        {{-- PHP & extensions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">PHP & extensions</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Version PHP</dt>
                    <dd class="text-slate-900 font-mono font-medium">{{ $systemInfo['php']['version'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">memory_limit</dt>
                    <dd class="text-slate-900 font-mono">{{ $systemInfo['php']['memory_limit'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">upload_max_filesize</dt>
                    <dd class="text-slate-900 font-mono">{{ $systemInfo['php']['upload_max_filesize'] }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">post_max_size</dt>
                    <dd class="text-slate-900 font-mono">{{ $systemInfo['php']['post_max_size'] }}</dd>
                </div>
                <div class="pt-2">
                    <dt class="text-slate-500 text-xs uppercase tracking-wider mb-2">Extensions critiques</dt>
                    <dd class="flex flex-wrap gap-1.5">
                        @foreach($systemInfo['php']['extensions'] as $name => $loaded)
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $loaded ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $name }}{{ $loaded ? '' : ' (manquant)' }}
                            </span>
                        @endforeach
                    </dd>
                </div>
            </dl>
        </div>

        {{-- OPcache --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900">OPcache</h3>
                @if($systemInfo['opcache']['enabled'])
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">Actif</span>
                @else
                    <span class="text-xs px-2.5 py-1 rounded-full bg-red-100 text-red-700">Inactif</span>
                @endif
            </div>
            @if($systemInfo['opcache']['enabled'])
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Scripts en cache</dt>
                        <dd class="text-slate-900 font-medium">{{ number_format($systemInfo['opcache']['cached_scripts'] ?? 0) }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <dt class="text-slate-500">Mémoire utilisée</dt>
                        <dd class="text-slate-900 font-medium">{{ $systemInfo['opcache']['memory_used'] ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Mémoire libre</dt>
                        <dd class="text-slate-900 font-medium">{{ $systemInfo['opcache']['memory_free'] ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                    <p class="font-semibold mb-1">⚠️ OPcache désactivé</p>
                    <p>Les performances en production seront fortement dégradées. Demande à ton hébergeur d'activer l'extension <code>opcache</code> dans la config PHP.</p>
                </div>
            @endif
        </div>

        {{-- Caches Laravel --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Caches Laravel (bootstrap/cache/)</h3>
            <dl class="space-y-2 text-sm">
                @foreach($systemInfo['caches'] as $name => $info)
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100 last:border-0">
                        <dt class="text-slate-500 font-mono text-xs">{{ $name }}.php</dt>
                        <dd>
                            @if($info['present'])
                                <span class="text-xs text-slate-600">{{ $info['mtime'] }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 ml-2">cache actif</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">absent</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            <p class="text-xs text-slate-500 mt-3">Ces fichiers sont régénérés par <code>deploy:after-pull</code>.</p>
        </div>

        {{-- Storage --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-900 mb-4">Stockage</h3>
            <dl class="space-y-2.5 text-sm">
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Lien public/storage</dt>
                    <dd>
                        @if($systemInfo['storage']['public_link_exists'])
                            <span class="text-emerald-600 font-medium">OK</span>
                        @else
                            <span class="text-red-600 font-semibold">Manquant</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">storage/app/public</dt>
                    <dd>
                        @if($systemInfo['storage']['storage_path_exists'])
                            <span class="text-emerald-600 font-medium">OK</span>
                        @else
                            <span class="text-red-600 font-semibold">Manquant</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Storage inscriptible</dt>
                    <dd>
                        @if($systemInfo['storage']['storage_writable'])
                            <span class="text-emerald-600 font-medium">Oui</span>
                        @else
                            <span class="text-red-600 font-semibold">Non (chmod -R 775 storage)</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Logs inscriptibles</dt>
                    <dd>
                        @if($systemInfo['storage']['logs_writable'])
                            <span class="text-emerald-600 font-medium">Oui</span>
                        @else
                            <span class="text-red-600 font-semibold">Non</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="text-xs text-slate-400 text-right">État relevé à {{ $systemInfo['now'] }}</div>
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
