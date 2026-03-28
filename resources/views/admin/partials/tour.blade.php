{{-- =====================================================
     SYSTÈME TUTORIEL GUIDÉ — Visite pas à pas
     Utilisation : @include('admin.partials.tour')
     Déclencher : window.dispatchEvent(new CustomEvent('start-tour', {detail:'dashboard'}))
     ===================================================== --}}

<style>
.tour-highlight {
    position: relative !important;
    z-index: 9998 !important;
    box-shadow: 0 0 0 3px #3b82f6, 0 0 0 7px rgba(59,130,246,0.25) !important;
    border-radius: 8px;
    transition: box-shadow 0.3s ease;
}
.tour-backdrop {
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(1px);
}
</style>

<div x-data="adminTour()"
     x-init="init()"
     @start-tour.window="start($event.detail)"
     @keydown.escape.window="close()">

    {{-- ===== BACKDROP ===== --}}
    <div x-show="active"
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="tour-backdrop fixed inset-0 z-[9990]"
         @click="close()"></div>

    {{-- ===== SÉLECTEUR DE TOUR ===== --}}
    <div x-show="showPicker"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed z-[9999] bottom-20 right-6 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
         @click.stop>
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Visites guidées</h3>
                <p class="text-xs text-slate-500 mt-0.5">Choisissez un tutoriel</p>
            </div>
            <button @click="showPicker=false" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-3 space-y-1">
            <template x-for="(tour, id) in tours" :key="id">
                <button @click="start(id); showPicker=false"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors text-left group">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors"
                         :style="{ background: tour.color + '20' }">
                        <span class="text-lg" x-text="tour.emoji"></span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-700" x-text="tour.name"></p>
                        <p class="text-xs text-slate-400" x-text="tour.steps.length + ' étapes'"></p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </template>
        </div>
    </div>

    {{-- ===== CARTE TUTORIEL (affiché pendant la visite) ===== --}}
    <div x-show="active"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed z-[9999] bottom-6 right-6 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
         @click.stop>

        {{-- Barre de progression --}}
        <div class="h-1 bg-slate-100">
            <div class="h-1 bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-500"
                 :style="{ width: ((step + 1) / currentTour().steps.length * 100) + '%' }"></div>
        </div>

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-base"
                 :style="{ background: currentTour().color + '20' }">
                <span x-text="currentTour().emoji"></span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-900 truncate" x-text="currentTour().name"></p>
                <p class="text-[10px] text-slate-400">
                    Étape <span x-text="step + 1"></span> / <span x-text="currentTour().steps.length"></span>
                </p>
            </div>
            <button @click="close()" class="p-1 hover:bg-slate-100 rounded-lg text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Contenu étape --}}
        <div class="p-4">
            <template x-if="currentStep().icon">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                    <span class="text-2xl" x-text="currentStep().icon"></span>
                </div>
            </template>
            <h3 class="font-bold text-slate-900 mb-1.5 text-sm" x-text="currentStep().title"></h3>
            <p class="text-sm text-slate-600 leading-relaxed" x-html="currentStep().body"></p>

            {{-- Lien de navigation --}}
            <template x-if="currentStep().url && !isCurrentPage(currentStep().url)">
                <a :href="currentStep().url"
                   class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    <span x-text="'Aller sur ' + currentStep().urlLabel"></span>
                </a>
            </template>

            {{-- Astuce --}}
            <template x-if="currentStep().tip">
                <div class="mt-3 flex items-start gap-2 px-3 py-2 bg-amber-50 rounded-xl border border-amber-100">
                    <span class="text-sm">💡</span>
                    <p class="text-xs text-amber-700" x-html="currentStep().tip"></p>
                </div>
            </template>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center justify-between px-4 pb-4 gap-2">
            <button @click="prev()"
                    x-show="step > 0"
                    class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Précédent
            </button>
            <div x-show="step === 0" class="flex-1"></div>
            <button @click="next()"
                    :class="step === currentTour().steps.length - 1 ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                    class="flex-1 px-4 py-2 text-sm font-semibold text-white rounded-xl transition-colors flex items-center justify-center gap-1.5">
                <span x-text="step === currentTour().steps.length - 1 ? '✓ Terminer' : 'Suivant'"></span>
                <svg x-show="step < currentTour().steps.length - 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- ===== BOUTON FLOTTANT ? ===== --}}
    <button @click="showPicker = !showPicker; if(active) close()"
            :class="showPicker || active ? 'bg-blue-600 text-white shadow-blue-600/30' : 'bg-white text-slate-600 shadow-slate-300/50 hover:bg-blue-50 hover:text-blue-600 hover:shadow-blue-200/50'"
            class="fixed bottom-6 right-6 z-[9989] w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-200 border border-slate-200 text-lg font-bold"
            x-show="!active"
            title="Aide & tutoriels">
        ?
    </button>
</div>

<script>
function adminTour() {
    const tours = {
        'decouverte': {
            name: 'Découverte du backoffice',
            emoji: '🏠',
            color: '#3b82f6',
            steps: [
                {
                    title: 'Bienvenue dans votre backoffice !',
                    body: 'Ce tutoriel vous guide à travers les fonctionnalités principales de votre boutique en ligne. Naviguez avec les boutons <strong>Suivant</strong> et <strong>Précédent</strong>.',
                    icon: '👋',
                    tip: 'Appuyez sur <strong>Échap</strong> à tout moment pour quitter.'
                },
                {
                    title: 'Le tableau de bord',
                    body: 'Le tableau de bord affiche vos statistiques clés : chiffre d\'affaires, commandes du jour, stock en alerte, et les dernières commandes.',
                    selector: 'nav a[href*="dashboard"]',
                    url: '/admin',
                    urlLabel: 'Tableau de bord',
                    icon: '📊',
                    tip: 'Les stats se mettent à jour automatiquement toutes les 30 secondes.'
                },
                {
                    title: 'Gérer les produits',
                    body: 'Créez vos produits avec titre, description, prix, images. Activez les <strong>variantes</strong> pour proposer différentes tailles ou couleurs.',
                    selector: 'nav a[href*="products"]',
                    url: '/admin/products',
                    urlLabel: 'Produits',
                    icon: '📦',
                    tip: 'Utilisez l\'<strong>ajout en masse</strong> dans l\'onglet Variantes pour ajouter toutes les tailles en une fois.'
                },
                {
                    title: 'Configurer les attributs',
                    body: 'Avant de créer des variantes, allez dans <strong>Attributs</strong> pour définir vos tailles (4 ans, 6 ans...), couleurs (Rouge, Bleu...) ou matières.',
                    selector: 'nav a[href*="attributes"]',
                    url: '/admin/attributes',
                    urlLabel: 'Attributs',
                    icon: '🏷️',
                    tip: 'Vous pouvez importer plusieurs valeurs en même temps : <em>4 ans, 6 ans, 8 ans, 10 ans</em>'
                },
                {
                    title: 'Gérer les commandes',
                    body: 'Chaque commande passe par des étapes : <strong>En attente → Confirmée → En préparation → Expédiée → Livrée</strong>. Mettez à jour le statut pour informer votre client.',
                    selector: 'nav a[href*="orders"]',
                    url: '/admin/orders',
                    urlLabel: 'Commandes',
                    icon: '🛒',
                },
                {
                    title: 'Paramètres essentiels',
                    body: 'Configurez votre boutique : nom de la boutique, email de contact, méthodes de paiement, et options de livraison.',
                    selector: 'nav a[href*="settings"]',
                    url: '/admin/settings',
                    urlLabel: 'Paramètres',
                    icon: '⚙️',
                    tip: 'Commencez par configurer votre <strong>email</strong> et vos <strong>méthodes de paiement</strong>.'
                }
            ]
        },
        'produit': {
            name: 'Créer un produit',
            emoji: '📦',
            color: '#8b5cf6',
            steps: [
                {
                    title: 'Créer un nouveau produit',
                    body: 'Cliquez sur <strong>Nouveau produit</strong> dans la liste des produits.',
                    url: '/admin/products/create',
                    urlLabel: 'Créer un produit',
                    icon: '➕',
                },
                {
                    title: 'Nom et description',
                    body: 'Remplissez le <strong>nom du produit</strong> (soyez précis : "Pantalon Cargo Enfant" plutôt que "Pantalon"), une <strong>description courte</strong> pour la liste et une description complète pour la page produit.',
                    selector: '#name',
                    icon: '✍️',
                    tip: 'Un bon nom de produit améliore le référencement et les ventes.'
                },
                {
                    title: 'Prix d\'achat et prix de vente',
                    body: 'Le <strong>prix d\'achat</strong> sert au calcul de vos marges. Le <strong>prix de vente</strong> est affiché au client. Le <strong>prix barré</strong> montre une ancienne réduction.',
                    selector: '#sale_price',
                    icon: '💰',
                },
                {
                    title: 'Activer les variantes',
                    body: 'Pour un produit avec plusieurs tailles ou couleurs, cochez <strong>"Produit avec variantes"</strong> dans la barre latérale. Sauvegardez puis allez dans l\'onglet <strong>Variantes</strong>.',
                    selector: 'input[name="has_variants"]',
                    icon: '🎨',
                    tip: 'Sans variantes, le stock est global. Avec variantes, chaque taille a son propre stock.'
                },
                {
                    title: 'Ajout en masse des variantes',
                    body: 'Dans l\'onglet <strong>Variantes → Ajout en masse</strong>, sélectionnez vos tailles et remplissez le stock de chacune. Un seul clic crée toutes les variantes.',
                    icon: '⚡',
                    tip: 'Exemple : 4 ans → 5 pcs, 6 ans → 10 pcs, 8 ans → 6 pcs.'
                },
                {
                    title: 'Ajouter des images',
                    body: 'Allez dans l\'onglet <strong>Images</strong>. Ajoutez plusieurs photos du produit. La première devient automatiquement l\'image principale.',
                    selector: 'button[\\@click*="images"]',
                    icon: '📸',
                    tip: 'Utilisez des photos de bonne qualité (min. 800x800px) sur fond blanc ou neutre.'
                },
                {
                    title: 'Publier !',
                    body: 'Mettez le statut sur <strong>Actif</strong> et cliquez <strong>Enregistrer</strong>. Votre produit est maintenant visible dans la boutique.',
                    selector: 'select[name="status"]',
                    icon: '🚀',
                }
            ]
        },
        'caisse': {
            name: 'Utiliser la caisse POS',
            emoji: '🏪',
            color: '#10b981',
            steps: [
                {
                    title: 'La caisse POS (Point de Vente)',
                    body: 'La caisse vous permet de vendre directement en boutique, scanner des articles et encaisser sans passer par la boutique en ligne.',
                    url: '/admin/scanner',
                    urlLabel: 'Caisse POS',
                    icon: '🏪',
                },
                {
                    title: 'Scanner un article',
                    body: 'Cliquez dans le champ de saisie et scannez le code-barres de l\'article. Vous pouvez aussi taper le <strong>SKU</strong> manuellement et appuyer sur Entrée.',
                    selector: '.scanner-input',
                    icon: '📱',
                    tip: 'Utilisez un scanner USB ou Bluetooth — ils fonctionnent comme un clavier.'
                },
                {
                    title: 'Scanner par caméra',
                    body: 'Cliquez sur le bouton <strong>Caméra</strong> pour utiliser la webcam ou la caméra du téléphone pour scanner un QR code ou code-barres.',
                    icon: '📷',
                },
                {
                    title: 'Le panier se remplit',
                    body: 'Chaque article scanné s\'ajoute au panier à droite. Vous pouvez modifier la quantité ou supprimer un article.',
                    icon: '🛒',
                    tip: 'Le sous-total et le total sont calculés automatiquement.'
                },
                {
                    title: 'Choisir le mode de paiement',
                    body: 'Sélectionnez le mode de paiement : <strong>Espèces</strong>, <strong>Carte</strong> ou <strong>Mobile Money</strong>.',
                    icon: '💳',
                    tip: 'Pour les espèces, entrez le montant remis par le client — la monnaie à rendre est calculée automatiquement.'
                },
                {
                    title: 'Encaisser et imprimer',
                    body: 'Cliquez sur <strong>Encaisser</strong>. Un reçu est généré automatiquement. Si vous avez une imprimante thermique configurée, il s\'imprime directement.',
                    icon: '🖨️',
                }
            ]
        },
        'bannieres': {
            name: 'Gérer les bannières',
            emoji: '🖼️',
            color: '#f59e0b',
            steps: [
                {
                    title: 'Les bannières de votre boutique',
                    body: 'Les bannières permettent de personnaliser l\'apparence de votre boutique : slider principal, barre d\'annonce promotionnelle, popup, etc.',
                    url: '/admin/banners',
                    urlLabel: 'Bannières',
                    icon: '🖼️',
                },
                {
                    title: 'Carte des positions',
                    body: 'La <strong>carte des positions</strong> montre où chaque bannière apparaît sur votre site. Les positions colorées ont au moins une bannière active.',
                    icon: '🗺️',
                    tip: 'Les hachures = aucune bannière active sur cette position.'
                },
                {
                    title: 'Créer une barre d\'annonce',
                    body: 'La barre d\'annonce apparaît <strong>tout en haut du site</strong>. Idéale pour : "🎉 Livraison gratuite dès 30 000 F CFA" ou les promotions en cours.',
                    url: '/admin/banners/create?position=announcement_bar',
                    urlLabel: 'Créer une annonce',
                    icon: '📢',
                    tip: 'L\'image n\'est pas obligatoire pour la barre d\'annonce — le texte seul suffit.'
                },
                {
                    title: 'Créer un slider hero',
                    body: 'Le slider <strong>hero</strong> est la grande image en haut de la page d\'accueil. Ajoutez plusieurs bannières hero pour créer un carrousel automatique.',
                    url: '/admin/banners/create?position=home_hero',
                    urlLabel: 'Créer un slider hero',
                    icon: '🌟',
                    tip: 'Taille recommandée : 1920x600px. Le titre et le bouton s\'affichent par-dessus l\'image.'
                },
                {
                    title: 'Activer / désactiver rapidement',
                    body: 'Dans la liste, chaque carte a un <strong>interrupteur</strong> (toggle) pour activer ou désactiver instantanément une bannière sans la supprimer.',
                    icon: '🔄',
                    tip: 'Pratique pour les promotions saisonnières — créez-les en avance et activez-les au bon moment.'
                },
                {
                    title: 'Planifier dans le temps',
                    body: 'Définissez une <strong>date de début</strong> et une <strong>date de fin</strong> pour qu\'une bannière s\'affiche automatiquement sur une période donnée.',
                    icon: '📅',
                    tip: 'Exemple : bannière pour l\'Aïd du 28/03 au 05/04 — activée et désactivée automatiquement.'
                }
            ]
        }
    };

    return {
        tours,
        active: false,
        showPicker: false,
        tourId: null,
        step: 0,
        _highlighted: null,

        init() {
            // Vérifier si un tour est en attente (après navigation)
            const pending = sessionStorage.getItem('pending_tour');
            if (pending) {
                sessionStorage.removeItem('pending_tour');
                this.$nextTick(() => setTimeout(() => this.start(pending), 600));
            }
        },

        start(id) {
            if (!this.tours[id]) return;
            this.tourId = id;
            this.step = 0;
            this.active = true;
            this.showPicker = false;
            this.$nextTick(() => this.applyHighlight());
        },

        currentTour() {
            return this.tours[this.tourId] || { name: '', emoji: '', color: '#3b82f6', steps: [{}] };
        },

        currentStep() {
            const steps = this.currentTour().steps;
            return steps[this.step] || {};
        },

        applyHighlight() {
            // Retirer l'ancien highlight
            if (this._highlighted) {
                this._highlighted.classList.remove('tour-highlight');
                this._highlighted = null;
            }
            const s = this.currentStep();
            if (s.selector) {
                const el = document.querySelector(s.selector);
                if (el) {
                    el.classList.add('tour-highlight');
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    this._highlighted = el;
                }
            }
        },

        next() {
            const steps = this.currentTour().steps;
            if (this.step < steps.length - 1) {
                this.step++;
                this.$nextTick(() => this.applyHighlight());
            } else {
                this.close();
                // Marquer le tour comme terminé
                try { localStorage.setItem('tour_done_' + this.tourId, '1'); } catch(e) {}
            }
        },

        prev() {
            if (this.step > 0) {
                this.step--;
                this.$nextTick(() => this.applyHighlight());
            }
        },

        close() {
            if (this._highlighted) {
                this._highlighted.classList.remove('tour-highlight');
                this._highlighted = null;
            }
            this.active = false;
            this.showPicker = false;
        },

        isCurrentPage(url) {
            return window.location.pathname === new URL(url, window.location.origin).pathname;
        }
    };
}
</script>
