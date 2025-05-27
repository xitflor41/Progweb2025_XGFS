#include <iostream>
#include <string>
using namespace std;

int main() {
    string cadena, patron;
    getline(cin, cadena);
    getline(cin, patron);

    int contador = 0;
    for (int i = 0; i <= cadena.size() - patron.size(); i++) {
        if (cadena.substr(i, patron.size()) == patron) {
            contador++;
        }
    }

    // Mostrar en binario (forma muy simple)
    string binario = "";
    int n = contador;
    if (n == 0) binario = "0";
    while (n > 0) {
        binario = char(n % 2 + '0') + binario;
        n /= 2;
    }

    // Convertir a número romano básico
    string romano = "";
    int numero = contador;

    while (numero >= 1000) { romano += "M"; numero -= 1000; }
    while (numero >= 900)  { romano += "CM"; numero -= 900; }
    while (numero >= 500)  { romano += "D"; numero -= 500; }
    while (numero >= 400)  { romano += "CD"; numero -= 400; }
    while (numero >= 100)  { romano += "C"; numero -= 100; }
    while (numero >= 90)   { romano += "XC"; numero -= 90; }
    while (numero >= 50)   { romano += "L"; numero -= 50; }
    while (numero >= 40)   { romano += "XL"; numero -= 40; }
    while (numero >= 10)   { romano += "X"; numero -= 10; }
    while (numero >= 9)    { romano += "IX"; numero -= 9; }
    while (numero >= 5)    { romano += "V"; numero -= 5; }
    while (numero >= 4)    { romano += "IV"; numero -= 4; }
    while (numero >= 1)    { romano += "I"; numero -= 1; }
    if(numero == 0){
        romano = "0"; 
    }

    cout << binario << endl;
    cout << romano << endl;

    return 0;
}