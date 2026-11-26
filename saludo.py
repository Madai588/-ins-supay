def saludar(nombre_completo):
    if not nombre_completo or not nombre_completo.strip():
        return "Hola, por favor ingresa un nombre"
    return f"Hola {nombre_completo}"

print(saludar("GitHub"))
