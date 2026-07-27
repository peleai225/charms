import { useForm as useInertiaForm } from '@inertiajs/vue3'

export function useForm(defaults) {
    const form = useInertiaForm(defaults)

    const hasError = (field) => !!form.errors[field]
    const errorMessage = (field) => form.errors[field] || null

    Object.defineProperty(form, 'hasError',    { value: hasError })
    Object.defineProperty(form, 'errorMessage',{ value: errorMessage })
    Object.defineProperty(form, 'isSubmitting', {
        get: () => form.processing,
        enumerable: true,
    })

    return form
}
