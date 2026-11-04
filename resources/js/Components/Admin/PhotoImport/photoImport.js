// Logique partagée de la page « Créer par photos » : couleurs des photos et grille des variantes.

export const slugify = (text) => String(text ?? '')
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '')

let nextPhotoId = 1
let nextCardId = 1

export function makePhoto(file, status) {
    return {
        id: nextPhotoId++,
        file,
        preview: URL.createObjectURL(file),
        status, // pending | analyzing | done | error | skipped
        error: null,
        suggestion: null,
        colorPick: '', // '' | id de couleur | 'new'
        newColorName: '',
        newColorHex: '#888888',
    }
}

export function makeCard(photos, fields = {}) {
    return {
        id: nextCardId++,
        photos,
        status: 'editing', // editing | creating | created
        autofilled: false,
        fields: {
            name: '',
            short_description: '',
            description: '',
            category_id: '',
            sale_price: '',
            purchase_price: '',
            stock_quantity: '',
            ...fields,
        },
        optionAttributeId: '',
        optionValueIds: [],
        optionsTouched: false,
        stock: {},
        selected: false,
        error: null,
        missing: false,
        editUrl: null,
        variantCount: 0,
    }
}

export const nameFromFile = (file) => file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ')

// Couleur proposée par l'IA → choix de la photo (sans écraser un choix manuel)
export function applyColorSuggestion(photo, s) {
    if (photo.colorPick !== '') return
    if (s.color_id) {
        photo.colorPick = s.color_id
    } else if (s.color_name) {
        photo.colorPick = 'new'
        photo.newColorName = s.color_name
        photo.newColorHex = s.color_hex || '#888888'
    }
}

// Clé de couleur d'une photo ; une « nouvelle » couleur qui existe déjà est ramenée à l'existante
export function photoColorKey(photo, colors) {
    if (photo.colorPick === 'new') {
        const name = photo.newColorName.trim()
        if (!name) return null
        const existing = colors.find((c) => slugify(c.value) === slugify(name))
        return existing ? `id:${existing.id}` : `new:${slugify(name)}`
    }
    return photo.colorPick !== '' && photo.colorPick !== null ? `id:${photo.colorPick}` : null
}

export function photoColorLabel(photo, colors) {
    if (photo.colorPick === 'new') return { label: photo.newColorName.trim(), hex: photo.newColorHex }
    const c = colors.find((c) => c.id === Number(photo.colorPick))
    return c ? { label: c.value, hex: c.color_code } : { label: '', hex: null }
}

// Tailles déjà utilisées dans la catégorie → pré-cochées, tant que l'admin n'a pas modifié la sélection
export function applyCategoryOptions(card, categoryOptions) {
    if (card.optionsTouched) return
    const s = categoryOptions[card.fields.category_id]
    card.optionAttributeId = s ? s.attribute_id : ''
    card.optionValueIds = s ? [...s.value_ids] : []
}

// Texte proposé par l'IA pour la fiche
export function applyTextSuggestion(card, s, categoryOptions) {
    if (s.name) card.fields.name = s.name
    if (s.short_description) card.fields.short_description = s.short_description
    if (s.description) card.fields.description = s.description
    if (s.category_id) card.fields.category_id = s.category_id
    card.autofilled = true
    applyCategoryOptions(card, categoryOptions)
}

// Plusieurs photos ou une déclinaison choisie → produit à variantes
export const isVariantMode = (card) => card.photos.length > 1 || card.optionValueIds.length > 0

// Couleurs distinctes du produit, chacune représentée par sa première photo
export function variantColors(card, colors) {
    const seen = new Map()
    card.photos.forEach((photo, index) => {
        const key = photoColorKey(photo, colors)
        if (key && !seen.has(key)) seen.set(key, { key, index, ...photoColorLabel(photo, colors) })
    })
    return [...seen.values()]
}

export function selectedOptionValues(card, optionAttributes) {
    const attr = optionAttributes.find((a) => a.id === Number(card.optionAttributeId))
    if (!attr) return []
    return attr.values.filter((v) => card.optionValueIds.includes(v.id))
}

// Toutes les combinaisons couleur × déclinaison
export function variantRows(card, colors, optionAttributes) {
    const colorList = variantColors(card, colors)
    const values = selectedOptionValues(card, optionAttributes)
    const rows = []
    for (const color of colorList.length ? colorList : [null]) {
        for (const value of values.length ? values : [null]) {
            if (!color && !value) continue
            rows.push({ key: `${color?.key ?? '-'}|${value?.id ?? '-'}`, color, value })
        }
    }
    return rows
}

// Erreurs bloquantes avant l'envoi
export function cardProblems(card, colors, optionAttributes) {
    const problems = []
    if (!card.fields.name.trim() || card.fields.sale_price === '') {
        problems.push('Le nom et le prix de vente sont obligatoires.')
    }
    if (card.photos.length > 1 && card.photos.some((p) => !photoColorKey(p, colors))) {
        problems.push('Choisissez la couleur de chaque photo.')
    }
    if (isVariantMode(card) && variantRows(card, colors, optionAttributes).length > 200) {
        problems.push('Trop de variantes (200 maximum) : retirez des tailles.')
    }
    return problems
}
