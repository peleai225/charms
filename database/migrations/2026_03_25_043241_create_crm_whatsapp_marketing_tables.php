<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags clients (VIP, fidele, nouveau, inactif, etc.)
        Schema::create('customer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_auto')->default(false);
            $table->json('auto_rules')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pivot tags <-> clients
        Schema::create('customer_customer_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_tag_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['customer_id', 'customer_tag_id']);
        });

        // Messages WhatsApp
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone');
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing');
            $table->enum('type', ['order_confirmation', 'shipping', 'delivery', 'abandoned_cart', 'promo', 'follow_up', 'custom', 'post_delivery', 'inactive_reminder'])->default('custom');
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
            $table->index(['type', 'status']);
        });

        // Campagnes marketing
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['whatsapp', 'email', 'push', 'sms'])->default('whatsapp');
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->text('message_template');
            $table->json('target_tags')->nullable();
            $table->json('target_filters')->nullable();
            $table->integer('recipients_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Automations marketing (workflows)
        Schema::create('marketing_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger', [
                'abandoned_cart', 'post_purchase', 'post_delivery',
                'inactive_customer', 'birthday', 'loyalty_milestone',
                'new_customer', 'vip_upgrade', 'custom'
            ]);
            $table->enum('channel', ['whatsapp', 'email', 'push', 'sms'])->default('whatsapp');
            $table->text('message_template');
            $table->integer('delay_hours')->default(0);
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sent_count')->default(0);
            $table->integer('converted_count')->default(0);
            $table->timestamps();
        });

        // Recommandations produits
        Schema::create('product_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recommended_product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['frequently_bought', 'similar', 'cross_sell', 'upsell'])->default('similar');
            $table->float('score')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'recommended_product_id', 'type'], 'product_reco_unique');
        });

        // Audio/video produits (accessibilite)
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['audio_description', 'video_demo', 'video_review']);
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->string('title')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('language', 5)->default('fr');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Ajout colonnes au client pour CRM
        Schema::table('customers', function (Blueprint $table) {
            $table->string('segment')->nullable()->after('customer_group');
            $table->decimal('lifetime_value', 12, 2)->default(0)->after('total_spent');
            $table->integer('purchase_frequency')->default(0)->after('lifetime_value');
            $table->decimal('avg_order_value', 10, 2)->default(0)->after('purchase_frequency');
            $table->decimal('return_rate', 5, 2)->default(0)->after('avg_order_value');
            $table->timestamp('last_activity_at')->nullable()->after('last_order_at');
            $table->string('preferred_channel', 20)->default('whatsapp')->after('accepts_marketing');
            $table->json('preferred_products')->nullable()->after('preferred_channel');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'segment', 'lifetime_value', 'purchase_frequency',
                'avg_order_value', 'return_rate', 'last_activity_at',
                'preferred_channel', 'preferred_products'
            ]);
        });

        Schema::dropIfExists('product_media');
        Schema::dropIfExists('product_recommendations');
        Schema::dropIfExists('marketing_automations');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('customer_customer_tag');
        Schema::dropIfExists('customer_tags');
    }
};
