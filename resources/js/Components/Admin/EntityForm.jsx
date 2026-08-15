import { router } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { DropZone, Field, Input, Select, Textarea } from './Form';

/**
 * Spec-driven form for the simpler admin entities.
 *
 * Each field is `{ name, label, type?, options?, placeholder?, hint?, defaultValue?, full? }`;
 * `type` defaults to a text input and also accepts `textarea`, `select`, `number`,
 * `email`, `tel`, `password` and `upload`. Fields lay out two per row unless
 * marked `full`.
 *
 * @param {{
 *   title: string,
 *   fields: object[],
 *   submitLabel: string,
 *   backHref: string,
 *   onSubmitTo?: string,
 * }} props
 */
export default function EntityForm({ title, fields, submitLabel, backHref, onSubmitTo }) {
    const submit = (event) => {
        event.preventDefault();

        router.visit(onSubmitTo ?? backHref);
    };

    return (
        <form onSubmit={submit} className="mx-auto max-w-3xl">
            <Card title={title}>
                <div className="grid gap-4 sm:grid-cols-2">
                    {fields.map((field) => {
                        const id = field.name;
                        const span = field.full || field.type === 'textarea' || field.type === 'upload'
                            ? 'sm:col-span-2'
                            : '';

                        return (
                            <Field
                                key={id}
                                label={field.label}
                                htmlFor={id}
                                hint={field.hint}
                                className={span}
                            >
                                {field.type === 'textarea' ? (
                                    <Textarea
                                        id={id}
                                        name={id}
                                        placeholder={field.placeholder}
                                        defaultValue={field.defaultValue}
                                    />
                                ) : field.type === 'select' ? (
                                    <Select
                                        id={id}
                                        name={id}
                                        options={field.options}
                                        defaultValue={field.defaultValue}
                                    />
                                ) : field.type === 'upload' ? (
                                    <DropZone hint={field.hint} />
                                ) : (
                                    <Input
                                        id={id}
                                        name={id}
                                        type={field.type ?? 'text'}
                                        placeholder={field.placeholder}
                                        defaultValue={field.defaultValue}
                                    />
                                )}
                            </Field>
                        );
                    })}
                </div>

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit">{submitLabel}</Button>
                    <Button href={backHref} variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
