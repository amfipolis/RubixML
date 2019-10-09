# Dataset Objects
In Rubix ML, data are passed in specialized in-memory containers called Dataset objects. Dataset objects are extended table-like data structures with an internal type system and many operations for wrangling. They can hold a heterogeneous mix of categorical and continuous data and they make it easy to transport data in a canonical way.

> **Note:** By convention, categorical data are given as string type whereas continuous data are given as either integer or floating point numbers.

### Selecting
Return all the samples in the dataset:
```php
public samples() : array
```

Select the *sample* at row offset:
```php
public row(int $index) : array
```

Select the *values* of a feature column at offset:
```php
public column(int $index) : array
```

### Properties
Return the number of rows in the dataset:
```php
public numRows() : int
```

Return the number of columns in the dataset:
```php
public numColumns() : int
```

Return the integer encoded column types for each feature column:
```php
public types() : array
```

Return the integer encoded column type given a column index:
```php
public columnType(int $index) : int
```

### Applying Transformations
You can apply a [Transformer](#transformers) directly to a Dataset by passing it to the `apply()` method on the dataset object.

```php
public apply(Transformer $transformer) : void
```

**Example**

```php
use Rubix\ML\Transformers\OneHotEncoder;

$dataset->apply(new OneHotEncoder());
```

You can also transform a single feature column using a callback function with the `transformColumn()` method.

```php
public transformColumn(int $column, callable $callback) : self
```

**Example**

```php
$dataset = $dataset->transformColumn(3, 'log1p'); // Log transform

$dataset = $dataset->transformColumn(15, function ($value) {
    return $value === '?' ? 'other' : $value; // Replace '?' with 'other'
});
```

### Stacking
Stack any number of dataset objects on top of each other to form a single dataset:
```php
public static stack(array $datasets) : self
```

**Example**

```php
use Rubix\ML\Datasets\Labeled;

$dataset = Labeled::stack([
    $training1,
    $training2,
    $testing,
]);
```

### Prepending and Appending
To prepend a given dataset onto the beginning of another dataset:
```php
public prepend(Dataset $dataset) : self
```

To append a given dataset onto the end of another dataset:
```php
public append(Dataset $dataset) : self
```

### Head and Tail
Return the *first* **n** rows of data in a new dataset object:
```php
public head(int $n = 10) : self
```

Return the *last* **n** rows of data in a new dataset object:
```php
public tail(int $n = 10) : self
```

**Example**

```php
// Return the first 5 rows in a new dataset
$subset = $dataset->head(5);

// Return the last 10 rows in a new dataset
$subset = $dataset->tail(10);
```

### Taking and Leaving
Remove **n** rows from the dataset and return them in a new dataset:
```php
public take(int $n = 1) : self
```

Leave **n** samples on the dataset and return the rest in a new dataset:
```php
public leave(int $n = 1) : self
```

### Slicing and Splicing
Return an *n* size portion of the dataset in a new dataset:
```php
public slice(int $offset, int $n) : self
```

Remove a size *n* chunk of the dataset starting at *offset* and return it in a new dataset:
```php
public splice(int $offset, int $n) : self
```

# Splitting
Split the dataset into left and right subsets given by a *ratio*:
```php
public split(float $ratio = 0.5) : array
```

Partition the dataset into left and right subsets based on the value of a feature in a specified column:
```php
public partition(int $index, mixed $value) : array
```

**Example**

```php
// Split the dataset into left and right subsets
[$left, $right] = $dataset->split(0.5);

// Partition the dataset by the feature column at index 4 by value '50'
[$left, $right] = $dataset->partition(4, 50);
```

### Folding
Fold the dataset *k* - 1 times to form *k* equal size datasets:
```php
public fold(int $k = 10) : array
```

**Example**

```php
// Fold the dataset into 8 equal size datasets
$folds = $dataset->fold(8);

var_dump(count($folds));
```

```sh
int(8)
```

### Batching
Batch the dataset into subsets containing a maximum of *n* rows per batch:
```php
public batch(int $n = 50) : array
```

### Randomization
Randomize the order of the Dataset and return it for method chaining:
```php
public randomize() : self
```

Generate a random subset without replacement:
```php
public randomSubset(int $n) : self
```

Generate a random subset with replacement of size *n*:
```php
public randomSubsetWithReplacement($n) : self
```

Generate a random *weighted* subset with replacement of size *n*:
```php
public randomWeightedSubsetWithReplacement($n, array $weights) : self
```

**Example**

```php
// Randomize and split the dataset and split into two subsets
[$left, $right] = $dataset->randomize()->split(0.8);

// Generate a random unique subset of 50 random samples
$subset = $dataset->randomSubset(50);

// Generate a 'bootstrap' dataset of 500 random samples
$subset = $dataset->randomSubsetWithReplacement(500);

// Sample a random subset according to a given weight distribution
$subset = $dataset->randomWeightedSubsetWithReplacement(200, $weights);
```

### Filtering
To filter a Dataset by a feature column:
```php
public filterByColumn(int $index, callable $fn) : self
```

**Example**

```php
$tallPeople = $dataset->filterByColumn(2, function ($value) {
	return $value > 178.5;
});
```

### Sorting
To sort a dataset in place by a specific feature column:
```php
public sortByColumn(int $index, bool $descending = false) : self
```

**Example**

```php
var_dump($dataset->samples());

$dataset->sortByColumn(2, false);

var_dump($dataset->samples());
```

```sh
array(3) {
    [0]=> array(3) {
	    [0]=> string(4) "mean"
	    [1]=> string(4) "furry"
	    [2]=> int(8)
    }
    [1]=> array(3) {
	    [0]=> string(4) "nice"
	    [1]=> string(4) "rough"
	    [2]=> int(1)
    }
    [2]=> array(3) {
	    [0]=> string(4) "nice"
	    [1]=> string(4) "rough"
	    [2]=> int(6)
    }
}

array(3) {
    [0]=> array(3) {
	    [0]=> string(4) "nice"
	    [1]=> string(4) "rough"
	    [2]=> int(1)
    }
    [1]=> array(3) {
	    [0]=> string(4) "nice"
	    [1]=> string(4) "rough"
	    [2]=> int(6)
    }
    [2]=> array(3) {
	    [0]=> string(4) "mean"
	    [1]=> string(4) "furry"
	    [2]=> int(8)
    }
}
```

### Descriptive Statistics
Return an array of statistics such as the central tendency, dispersion and shape of each continuous feature column and the joint probabilities of every categorical feature column:
```php
public describe() : array
```

**Example**

```php
$stats = $dataset->describe();

print_r($stats);
```

```sh
Array
(
	...

    [2] => Array
        (
            [column] => 2
            [type] => categorical
            [num_categories] => 2
            [probabilities] => Array
                (
                    [friendly] => 0.66666666666667
                    [loner] => 0.33333333333333
                )

        )

    [3] => Array
        (
            [column] => 3
            [type] => continuous
            [mean] => 0.33333333333333
            [variance] => 9.7922222222222
            [std_dev] => 3.1292526619342
            [skewness] => -0.44810308436906
            [kurtosis] => -1.1330702741786
            [min] => -5
            [25%] => -1.375
            [median] => 0.8
            [75%] => 2.825
            [max] => 4
        )

)
```

### De-duplication
Return a dataset with all duplicate rows removed:
```php
public deduplicate() : self
```