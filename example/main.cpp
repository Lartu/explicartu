#include<iostream>

using namespace std;

//(project-title) Demo Explicartu Project
//(project-description) A demo that showcasts all the features of Explicartu!

//(var number int) An inconspicuous number variable.
int number = 10;

//(var number string) A string variable that stores the string "Hi there!". Used in the function printHi.
string hiString = "Hi there!";

//(function) printHi
//(does) Prints a string as many times as told to.
//(parameter times int) How many times to print the string.
//(parameter saySomething string) The string to be printed.
void printHi(int times, string saySomething){
    for(int i = 0; i < times; ++i)
        cout << saySomething;
}
//(end-function)

//(function) main
//(does) Prints 'Hello World' and calls 'printHi'.
//(returns int) Returns 1 and exits.
//(important) This funcion returns 1, not 0!
//(end-function)
int main(){
    cout << "Hello World!";
    printHi(number, hiString);
    return 1;
}

//(class) myClass
//(is) A demo class with no use whatsoever!
//(todo) Make this class greater.
class myClass{

private:
    
    //(var isThisClassGreat bool) Stores if this class is great of not (default: true).
    bool isThisClassGreat = true;
    
    //(var someNumber float) Some other number.
    float someNumber = 100;
    
public:
    
    //(function) addNumbers
    //(does) Adds a number to the property 'someNumber'.
    //(parameter otherNumber int) Number to add.
    //(details) You can add details to functions and classes.
    //(end-function)
    void addNumbers(int otherNumber){
        this->someNumber += otherNumber;
    }
    
    //(function) myClass
    //(does) Constructor of this class. Does nothing then dies.x
    //(end-function)
    myClass(){}
    
};
//(end-class)
