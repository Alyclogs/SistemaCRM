const baseurl = 'http://localhost/SistemaCRM/';

export const icons = async () => {
    const [edit, menu, telefono, correo, dni] = await Promise.all([
        fetch(baseurl + "assets/svg/edit.svg").then(res => res.text()),
        fetch(baseurl + "assets/svg/menu.svg").then(res => res.text()),
        fetch(baseurl + "assets/svg/call.svg").then(res => res.text()),
        fetch(baseurl + "assets/svg/sms.svg").then(res => res.text()),
        fetch(baseurl + "assets/svg/document-text-2.svg").then(res => res.text())
    ]);

    return {
        edit,
        menu,
        telefono,
        correo,
        dni
    };
};