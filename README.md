# NavyBayes-class

## Inclusão da Classe

```php
    include_once "diretorio/NavyBayes.class.php";
```

Este algoritmo é ótimo para classificação de textos com base em categorias pré definidas, por exemplo, saber se um texto é "Positivo", "Negativo" ou "Neutro" e se preferir "Inclassificavel", onde o algoritmo não consegue encontrar uma das categorias anteriores.

Obs: A opção "Inclassificavel" é totalmente opcional e se informada, deve receber um treino VAZIO, pois ela serve como um "ESCAPE" para o algoritmo, ou seja, no caso do algoritmo não conseguir classificar o texto (por faltar dados de treino [NOTA: É necessário muitos dados de treino para que o algoritmo comece a categorizar corretamente caso escolha colocar essa função de ESCAPE, com poucos dados, para não errar muito o algoritmo irá categorizar praticamente todos os textos nessa categoria!]) ele irá classificar este texto com essa opção vazia. Caso ela não exista, o classificador tentará errar menos e retorna-rá uma das 3 outras categorias restantes ("Positivo", "Negativo" ou "Neutro"), mas provavelmnete o resultado será impreciso.

Obs2: É possivel cadastrar "N" categorias com nomencaturas diferentes, as citadas aqui são apenas para fins de exemplo.

## Chamada da Classe

```php
    $classification = new NavyBayes();
```

### Treinando o algoritmo

```php
    $classification = new NavyBayes();

    $categories = Array(
        "Positivo" => "ótimo,perfeito,lindo,inteligente,maravilhoso",
        "Negativo" => "burro,idiota,retardado,chato,entediante",
        "Neutro" => "legal,bom,interessante",
        // "Inclassificavel" => "" //Optional
    );

    $classification->learn($categories["Positivo"], "Positivo");
    $classification->learn($categories["Negativo"], "Negativo");
    $classification->learn($categories["Neutro"], "Neutro");
    //$classification->learn($categories["Inclassificavel"], "Inclassificavel"); //Optional

```

### Obtendo a classificação após o treino

```php
    $classification = new NavyBayes();

    $categories = Array(
        "Positivo" => "ótimo, perfeito, lindo, inteligente, maravilhoso",
        "Negativo" => "muito burro, idiota, retardado, chato, entediante",
        "Neutro" => "legal, bom, interessante",
        //"Inclassificavel" => "" //Optional
    );

    $classification->learn($categories["Positivo"], "Positivo");
    $classification->learn($categories["Negativo"], "Negativo");
    $classification->learn($categories["Neutro"], "Neutro");
    //$classification->learn($categories["Inclassificavel"], "Inclassificavel"); //Optional

    echo $classification->categorize("O João é muito maravilhoso!"); // Positivo
    echo $classification->categorize("Meu Deus, como esse menino é muito burro!"); // Negativo
```

### Treinando com frases

```php
    $classification = new NavyBayes();

    $categories = Array(
        "Sim" => "como ele é lindo, menino de ouro!, que fruta deliciosa",
        "Nao" => "ele é feio demais!, se ferrou!, que delícia de abobora",
        "Talvez" => "isso é interessante, isso me parece sem graça",
        "Pode_ser" => "TREINO AQUI",
        "Nunca" => "TREINO AQUI",
        "Sempre" => "TREINO AQUI",
    );

    $classification->learn($categories["Sim"], "Sim");
    $classification->learn($categories["Nao"], "Não");
    $classification->learn($categories["Talvez"], "Talvez");
    $classification->learn($categories["Pode_ser"], "Pode ser");
    $classification->learn($categories["Nunca"], "Nunca");
    $classification->learn($categories["Sempre"], "Sempre");
```

Aproveitem!
